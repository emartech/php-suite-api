<?php

namespace Suite\Api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

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
     * @var EscherProvider
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

    /**
     * @var string (http|https)
     */
    private $protocol = self::HTTPS;


    public static function create(LoggerInterface $logger, EscherProvider $escherProvider, ResponseProcessor $responseProcessor, $protocol = self::HTTPS)
    {
        return new self($logger, $escherProvider, new \GuzzleHttp\Client(), new RequestFactory(), $responseProcessor, $protocol);
    }

    public function __construct(LoggerInterface $logger, EscherProvider $escherProvider, ClientInterface $client, RequestFactory $requestFactory, ResponseProcessor $responseProcessor, $protocol = self::HTTPS)
    {
        $this->logger = $logger;
        $this->escherProvider = $escherProvider;
        $this->client = $client;
        $this->responseProcessor = $responseProcessor;
        $this->protocol = $protocol;
        $this->requestFactory = $requestFactory;
    }

    public function get($url)
    {
        $method = 'GET';
        $url = "$this->protocol://$url";
        $requestBody = '';
        $headers = $this->getHeaders($url, $method, $requestBody);
        return $this->executeRequest($this->createRequest($method, $url, $headers, $requestBody));
    }

    public function post($url, $data)
    {
        $method = 'POST';
        $url = "$this->protocol://$url";
        $requestBody = $this->getBody($data);
        $headers = $this->getHeaders($url, $method, $requestBody);
        return $this->executeRequest($this->createRequest($method, $url, $headers, $requestBody));
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws Error
     */
    private function executeRequest(RequestInterface $request = null)
    {
        try {
            $this->logger->info('Executing request:' . $this->serializeRequestForLogging($request));
            $response = $this->client->send($request);
            $this->logger->info('Success:' . $response->getBody());
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

    private function serializeRequestForLogging(RequestInterface $request)
    {
        return "{$request->getMethod()} {$request->getUri()}";
    }
}
