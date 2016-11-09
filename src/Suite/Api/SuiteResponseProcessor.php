<?php

namespace Suite\Api;


use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Psr\Log\LoggerInterface;

class SuiteResponseProcessor implements ResponseProcessor
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function processResponse(RequestInterface $request, Response $response): array
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
