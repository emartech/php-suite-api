<?php

namespace Suite\Api\Email;

use Suite\Api\Client;
use Suite\Api\RequestFailed;


class Launch
{
    /** @var Client */
    private $apiClient;

    /** @var EndPoints */
    private $endPoints;


    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }


    public function launch(int $customerId, int $campaignId)
    {
        try
        {
            $response = $this->apiClient->post($this->endPoints->emailLaunch($customerId, $campaignId), ['emailId' => $campaignId]);
            return $response['data'];
        }
        catch (\Exception $ex)
        {
            throw new RequestFailed('Could not launch email: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}