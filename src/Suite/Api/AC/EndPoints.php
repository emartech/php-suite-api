<?php

namespace Suite\Api\AC;

class EndPoints
{
    private string $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function programCallbackDoneUrl(int $customerId, string $triggerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks/{$triggerId}";
    }

    public function programBatchCallbackDoneUrl(int $customerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks";
    }

    public function programCallbackCancelUrl(int $customerId, string $triggerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks/{$triggerId}/cancel";
    }
}

