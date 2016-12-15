<?php

namespace Suite\Api\Email;

class EndPoints
{
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    private function getCampaignBaseUrl(int $customerId): string
    {
        return "{$this->apiBaseUrl}/{$customerId}/email/";
    }

    public function emailCampaign(int $customerId, int $campaignId): string
    {
        return $this->getCampaignBaseUrl($customerId)."{$campaignId}";
    }

    public function emailPreview(int $customerId, int $campaignId): string
    {
        return $this->emailCampaign($customerId, $campaignId)."preview";
    }

    public function emailCampaignList(int $customerId): string
    {
        return $this->getCampaignBaseUrl($customerId);
    }
}
