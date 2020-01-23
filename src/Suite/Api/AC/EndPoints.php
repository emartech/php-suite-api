<?php

namespace Suite\Api\AC;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function programCallbackUrl(int $customerId, string $triggerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks/{$triggerId}";
    }
}

