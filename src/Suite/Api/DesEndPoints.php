<?php

namespace Suite\Api;

class DesEndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function desScore(int $customerId): string
    {
        return "{$this->apiBaseUrl}/score/{$customerId}";
    }
}
