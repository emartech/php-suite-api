<?php

namespace Suite\Api\Email;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function emailCampaign(int $customerId, int $campaignId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/email/{$campaignId}";
    }

    public function emailPreview(int $customerId, int $campaignId): string
    {
        return $this->emailCampaign($customerId, $campaignId)."/preview";
    }
}
