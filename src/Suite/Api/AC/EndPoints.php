<?php

namespace Suite\Api\AC;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function programCallbackDoneUrl(int $customerId, string $triggerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks/{$triggerId}";
    }

    public function programCallbackCancelUrl(int $customerId, string $triggerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks/{$triggerId}/cancel";
    }
}

