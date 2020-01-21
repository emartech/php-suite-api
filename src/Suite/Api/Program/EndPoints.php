<?php

namespace Suite\Api\Program;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function callbackUrl(int $customerId, string $triggerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/ac/programs/callbacks/{$triggerId}";
    }
}
