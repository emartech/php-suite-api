<?php

namespace Suite\Api;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class SuiteResponseProcessor implements ResponseProcessor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array
     * @throws Error
     */
    public function processResponse(RequestInterface $request, ResponseInterface $response): array
    {
        $responseBody = $response->getBody();
        $result = json_decode($responseBody, true);

        if (!$result) {
            $this->logger->error('Bad API response', [ 'uri' => $request->getRequestTarget() ]);
            $this->logger->debug('Bad API response', [
                'uri' => $request->getRequestTarget(),
                'reponseBody' => $responseBody,
            ]);
            throw new Error(self::API_RESPONSE_FORMAT_WAS_WRONG);
        }

        $result['success'] = isset($result['replyCode']) && $result['replyCode'] === 0;

        if (!$result['success']) {
            $replyText = isset($result['replyText']) ? $result['replyText'] : self::UNKNOWN_ERROR;
            $replyCode = isset($result['replyCode']) ? $result['replyCode'] : 0;

            $this->logger->error(
                "Unsuccessful API response",
                [
                    'uri' => $request->getRequestTarget(),
                    'replyText' => $replyText,
                    'replyCode' => $replyCode,
                ]
            );

            $this->logger->debug(
                "Unsuccessful API response",
                [
                    'uri' => $request->getRequestTarget(),
                    'replyText' => $replyText,
                    'replyCode' => $replyCode,
                    'responseBody' => $responseBody,
                ]
            );

            throw new Error($replyText, $replyCode);
        }

        return $result;
    }
}
