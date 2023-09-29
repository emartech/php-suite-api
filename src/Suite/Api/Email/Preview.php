<?php

namespace Suite\Api\Email;

use Suite\Api\Client;
use Suite\Api\RequestFailed;

class Preview
{
    const VERSION_HTML = 'html';
    const VERSION_TEXT = 'text';
    const VERSION_MOBILE = 'mobile';

    /** @var Client */
    private $apiClient;

    /** @var EndPoints */
    private $endPoints;


    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }


    public function get(int $customerId, int $campaignId, string $version)
    {
        try
        {
            $response = $this->apiClient->post($this->endPoints->emailPreview($customerId, $campaignId), ['version' => $version]);
            return $response['data'];
        }
        catch (\Exception $ex)
        {
            throw new RequestFailed('Could not get preview for email: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }


    public function getHtml(int $customerId, int $campaignId)
    {
        return $this->get($customerId, $campaignId, self::VERSION_HTML);
    }


    public function getText(int $customerId, int $campaignId)
    {
        return $this->get($customerId, $campaignId, self::VERSION_TEXT);
    }

    public function getMobile(int $customerId, int $campaignId)
    {
        return $this->get($customerId, $campaignId, self::VERSION_MOBILE);
    }
}

