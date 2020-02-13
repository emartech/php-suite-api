<?php

namespace Suite\Api;

use Escher\Provider as EscherProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Suite\Api\Middleware\Retry;

class Client
{
    const HTTP  = 'http';
    const HTTPS = 'https';
    const COULD_NOT_EXECUTE_API_REQUEST = 'Could not execute API request.';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Escher\Provider
     */
    private $escherProvider;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

	/**
	 * @var ResponseProcessor
	 */
    private $responseProcessor;


    public static function create(LoggerInterface $logger, EscherProvider $escherProvider, ResponseProcessor $responseProcessor)
    {
        return new self($logger, $escherProvider, new \GuzzleHttp\Client(), new RequestFactory(), $responseProcessor);
    }

    public static function createWithRetry(LoggerInterface $logger, EscherProvider $escherProvider, ResponseProcessor $responseProcessor, int $retryCount = 1)
    {
        $handler = (new Retry($logger, $retryCount))->createHandler();
        $stack   = HandlerStack::create(new CurlHandler());
        $stack->push(Middleware::retry($handler), 'retry');

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
        ]);

        return new self($logger, $escherProvider, $client, new RequestFactory(), $responseProcessor);
    }

    public function __construct(LoggerInterface $logger, EscherProvider $escherProvider, ClientInterface $client, RequestFactory $requestFactory, ResponseProcessor $responseProcessor)
    {
        $this->logger = $logger;
        $this->escherProvider = $escherProvider;
        $this->client = $client;
        $this->responseProcessor = $responseProcessor;
        $this->requestFactory = $requestFactory;
    }

    public function get(string $url, array $parameters = array())
    {
        $method = 'GET';
        $requestBody = '';
        $fullUrl = $this->buildUrlWithParameters($url, $parameters);
        $headers = $this->getHeaders($fullUrl, $method, $requestBody);
        return $this->executeRequest($this->createRequest($method, $fullUrl, $headers, $requestBody));
    }

    public function post(string $url, $data)
    {
        return $this->sendRequestWithBody($url, $data, 'POST');
    }

    public function put(string $url, $data)
    {
        return $this->sendRequestWithBody($url, $data, 'PUT');
    }

    private function sendRequestWithBody(string $url, $data, $method)
    {
        $requestBody = $this->getBody($data);
        $headers = $this->getHeaders($url, $method, $requestBody);
        return $this->executeRequest($this->createRequest($method, $url, $headers, $requestBody));
    }

    private function executeRequest(RequestInterface $request = null)
    {
        try {
            $this->logRequest($request, 'executing');
            $response = $this->client->send($request, [
                'on_stats' => function (TransferStats $stats) use ($request) {
                    $this->logRequest($request, 'stats', $stats);
                }
            ]);
            $this->logRequest($request, 'successful');
            return $this->responseProcessor->processResponse($request, $response);
        } catch (BadResponseException $ex) {
            $this->logger->error($ex->getMessage());
            return $this->responseProcessor->processResponse($request, $ex->getResponse());
        } catch (RequestException $ex) {
            $this->logger->error($ex->getMessage());
            throw new Error(self::COULD_NOT_EXECUTE_API_REQUEST);
        }
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $requestBody
     * @return array
     */
    protected function getHeaders($url, $method, $requestBody)
    {
        return $this->escherProvider->createEscher()
            ->signRequest(
                $this->escherProvider->getEscherKey(),
                $this->escherProvider->getEscherSecret(),
                $method,
                $url,
                $requestBody,
                ['Content-Type' => 'application/json']
            );
    }

    /**
     * @param $data
     * @return string
     */
    protected function getBody($data)
    {
        return json_encode($data);
    }

    /**
     * @param $method
     * @param $url
     * @param $headers
     * @param $requestBody
     * @return RequestInterface
     */
    private function createRequest($method, $url, $headers, $requestBody)
    {
        return $this->requestFactory->createRequest($method, $url, $headers, $requestBody);
    }

    /**
     * @param string $url
     * @param array $data
     * @return string
     */
    private function buildUrlWithParameters(string $url, array $data): string
    {
        if (!empty($data))
        {
            return $url . '?' . http_build_query($data);
        }
        else
        {
            return $url;
        }
    }

    private function logRequest(RequestInterface $request, string $event, TransferStats $stats = null)
    {
        $message = [
            'type'    => 'suite_api_client',
            'action'  => 'request',
            'host'    => $request->getUri()->getHost(),
            'uri'     => (string)$request->getUri(),
            'method'  => $request->getMethod()
        ];

        if ($stats) {
            $message += [ 'time' => (int) ($stats->getTransferTime() * 1000) ];
        }

        $this->logger->debug("request $event", $message);
    }
}
