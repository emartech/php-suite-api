<?php

namespace Suite\Api\Contact;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function getData(int $customerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/contact/getdata";
    }
}
