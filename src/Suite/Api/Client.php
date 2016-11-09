<?php

namespace Suite\Api;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
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
	 * @var ResponseProcessor
	 */
    private $responseProcessor;

    /**
     * @var string (http|https)
     */
    private $protocol = self::HTTPS;


    public static function create(LoggerInterface $logger, EscherProvider $escherProvider, ResponseProcessor $responseProcessor, $protocol = self::HTTPS)
    {
        return new self($logger, $escherProvider, new \Guzzle\Http\Client(), $responseProcessor, $protocol);
    }

    public function __construct(LoggerInterface $logger, EscherProvider $escherProvider, ClientInterface $client, ResponseProcessor $responseProcessor, $protocol = self::HTTPS)
    {
        $this->logger = $logger;
        $this->escherProvider = $escherProvider;
        $this->client = $client;
        $this->responseProcessor = $responseProcessor;
        $this->protocol = $protocol;
    }

    public function get($url)
    {
        $url = "$this->protocol://$url";
        $method = 'GET';
        $requestBody = '';
        $headers = $this->getHeaders($url, $method, $requestBody);
        return $this->executeRequest($this->client->get($url, $headers));
    }

    public function post($url, $data)
    {
        $url = "$this->protocol://$url";
        $method = 'POST';
        $requestBody = $this->getBody($data);
        $headers = $this->getHeaders($url, $method, $requestBody);
        return $this->executeRequest($this->client->post($url, $headers, $requestBody));
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws Error
     */
    private function executeRequest(RequestInterface $request = null)
    {
        try {
            $this->logger->info('Executing request:' . (string)$request);
            $response = $request->send();
            $this->logger->info('Success:' . $response->getBody(true));
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
}
