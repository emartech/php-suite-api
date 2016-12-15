<?php

namespace Suite\Api\ExternalEvent;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class ExternalEvent
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * @var EndPoints
     */
    private $endPoints;

    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }

    /**
     * @param int $customerId
     * @return array
     * @throws RequestFailed
     */
    public function getList(int $customerId) : array
    {
        try {
            return $this->apiClient->get($this->endPoints->getList($customerId))['data'];
        } catch (Error $error) {
            throw new RequestFailed("Could not load external events: {$error->getMessage()}", $error->getCode(), $error);
        }
    }
}
