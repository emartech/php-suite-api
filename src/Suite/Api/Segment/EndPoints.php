<?php

namespace Suite\Api\Segment;

class EndPoints
{
    /**
     * @var string
     */
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function getList(int $customerId)
    {
        return "{$this->apiBaseUrl}/{$customerId}/filter";
    }
}
