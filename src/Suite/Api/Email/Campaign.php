<?php

namespace Suite\Api\Email;

use Psr\Log\LoggerInterface;
use Suite\Api\Client;
use Suite\Api\RequestFailed;

class Campaign
{
    /* @var Client */
    private $apiClient;

    /* @var EndPoints */
    private $endPoints;


    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }


    public function getById(int $customerId, int $campaignId)
    {
        try
        {
            $response = $this->apiClient->get($this->endPoints->emailCampaign($customerId, $campaignId));
            return $response['data'];
        }
        catch (\Exception $ex)
        {
            throw new RequestFailed('Could not get details for email: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }


    public function getList(int $customerId, array $filter = [])
    {
        try
        {
            $response = $this->apiClient->get($this->endPoints->emailCampaignList($customerId, $filter));
            return $response['data'];
        }
        catch (\Exception $ex)
        {
            throw new RequestFailed('Could not get details for email list: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}


