<?php

namespace Suite\Api;


use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Psr\Log\LoggerInterface;

class DesResponseProcessor implements ResponseProcessor
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function processResponse(RequestInterface $request, Response $response): array
    {
        $responseBody = $response->getBody(true);
        $responseCode = $response->getStatusCode();
        $result = json_decode($responseBody, true);

        if ($result === false) {
            $this->logger->error("Unsuccessful API response for {$request->getUrl()}. responseBody: '{$responseBody}', responseCode: {$responseCode}");
            $this->logger->debug("API response was: {$responseBody}");
            throw new Error($responseBody, $responseCode);
        }

        return $result;
    }
}
