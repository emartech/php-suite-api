<?php

namespace Suite\Api\Email;

use Suite\Api\Client;
use Suite\Api\RequestFailed;
use Suite\Api\Error;

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
        catch (Error $ex)
        {
            throw new RequestFailed('Could not get details for email: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }


    public function getList(int $customerId, array $filter = [])
    {
        try
        {
            $response = $this->apiClient->get($this->endPoints->emailCampaignList($customerId), $filter);
            return $response['data'];
        }
        catch (Error $ex)
        {
            throw new RequestFailed('Could not get details for email list: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function deleteById(int $customerId,int $campaignId)
    {
        try
        {
            $response = $this->apiClient->post($this->endPoints->emailCampaignDelete($customerId), [
                'emailId' => $campaignId
            ]);
            return $response['data'];
        }
        catch (Error $ex)
        {
            throw new RequestFailed('Could not delete email list: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}


