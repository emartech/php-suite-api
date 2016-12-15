<?php

namespace Suite\Api\Administrator;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function administratorList(int $customerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/administrator";
    }
}
