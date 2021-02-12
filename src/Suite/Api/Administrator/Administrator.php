<?php

namespace Suite\Api\Administrator;


use Suite\Api\Client;
use Suite\Api\RequestFailed;
use Suite\Api\Error;

class Administrator
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

    public function getList(int $customerId) : array
    {
        try
        {
            $response = $this->apiClient->get($this->endPoints->administratorList($customerId));
            return $response['data'];
        }
        catch (Error $ex)
        {
            throw new RequestFailed('Could not get list of administrators: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
