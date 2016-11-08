<?php

namespace Suite\Api;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Psr\Log\LoggerInterface;

class Client
{
    const HTTP  = 'http';
    const HTTPS = 'https';
    const COULD_NOT_EXECUTE_API_REQUEST = 'Could not execute API request.';
    const API_RESPONSE_FORMAT_WAS_WRONG = 'API response format was wrong';
    const UNKNOWN_ERROR = 'Unknown error';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var EscherProvider
     */
    private $escherProvider;

    /**
     * @var string (http|https)
     */
    private $protocol = self::HTTPS;

    private $client;

    public function __construct(LoggerInterface $logger, EscherProvider $escherProvider, ClientInterface $client)
    {
        $this->logger = $logger;
        $this->escherProvider = $escherProvider;
        $this->client = $client;
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
            return $this->processResponse($request, $response);
        } catch (BadResponseException $ex) {
            $this->logger->error($ex->getMessage());
            return $this->processResponse($request, $ex->getResponse());
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
     * @param RequestInterface $request
     * @param $response
     * @return mixed
     * @throws Error
     */
    private function processResponse(RequestInterface $request, Response $response)
    {
        $responseBody = $response->getBody(true);
        $result = json_decode($responseBody, true);

        if (!$result) {
            $this->logger->error('Bad API response for ' . $request->getUrl());
            $this->logger->debug("API response was: {$responseBody}");
            throw new Error(self::API_RESPONSE_FORMAT_WAS_WRONG);
        }

        $result['success'] = isset($result['replyCode']) && $result['replyCode'] === 0;

        if (!$result['success']) {
            $replyText = isset($result['replyText']) ? $result['replyText'] : self::UNKNOWN_ERROR;
            $replyCode = isset($result['replyCode']) ? $result['replyCode'] : 0;
            $this->logger->error("Unsuccessful API response for {$request->getUrl()}. replyText: '{$replyText}', replyCode: {$replyCode}");
            $this->logger->debug("API response was: {$responseBody}");
            throw new Error($replyText, $replyCode);
        }

        return $result;
    }
}
