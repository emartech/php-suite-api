<?php

namespace Suite\Api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class DesResponseProcessor implements ResponseProcessor
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function processResponse(RequestInterface $request, ResponseInterface $response): array
    {
        $responseBody = $response->getBody();
        $responseCode = $response->getStatusCode();
        $result = json_decode($responseBody, true);

        if ($result === false) {
            $this->logger->error("Unsuccessful API response for {$request->getUri()}. responseBody: '{$responseBody}', responseCode: {$responseCode}");
            $this->logger->debug("API response was: {$responseBody}");
            throw new Error($responseBody, $responseCode);
        }

        return $result;
    }
}
