<?php

namespace Suite\Api\Segment;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class Segment
{
    private $apiClient;
    private $endPoints;

    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }

    public function getList(int $customerId) : array
    {
        try {
            return $this->apiClient->get($this->endPoints->getList($customerId))['data'];
        } catch (Error $error) {
            throw new RequestFailed("Could not load segments: {$error->getMessage()}", $error->getCode(), $error);
        }
    }
}
