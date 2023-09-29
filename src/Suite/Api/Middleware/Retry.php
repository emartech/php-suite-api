<?php

namespace Suite\Api\Middleware;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

class Retry
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var int
     */
    private $maxRetryCount;

    public function __construct(LoggerInterface $logger, int $maxRetryCount = 1)
    {
        $this->logger        = $logger;
        $this->maxRetryCount = $maxRetryCount;
    }

    public function createHandler()
    {
        return function (int $retries, Request $request, Response $response = null, TransferException $exception = null) {
            if ($this->stillHasRetryAttempts($retries) && $this->isRetriableError($response, $exception)) {
                $this->log($retries, $request, $response, $exception);
                return true;
            }
            return false;
        };
    }

    private function isRetriableError($response, $exception = null)
    {
        return $this->isServerError($response) || $this->isConnectError($exception);
    }

    private function stillHasRetryAttempts(int $retries): bool
    {
        return $retries < $this->maxRetryCount;
    }

    private function isServerError(Response $response = null)
    {
        return $response && $response->getStatusCode() >= 500;
    }

    private function isConnectError(TransferException $exception = null)
    {
        return $exception instanceof ConnectException;
    }

    private function log(int $retries, Request $request, Response $response = null, TransferException $exception = null)
    {
        $this->logger->warning(
            'Retrying API call',
            [
                'http' => [
                    'request' => [
                        'method' => $request->getMethod(),
                    ],
                    'response' => $response ? ['status_code' => $response->getStatusCode()] : [],
                ],
                'url' => [
                    'full' => $request->getUri(),
                ],
                'retries' => $retries + 1,
                'max_retry_count' => $this->maxRetryCount,
            ] + ($exception ? ['error' => $exception] : [])
        );
    }
}
