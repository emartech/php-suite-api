<?php

namespace Suite\Api\AC;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class Program
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

    public function programCallbackWithUserId(int $customerId, string $triggerId, int $userId)
    {
        $this->programCallback($customerId, $triggerId, $userId, null);
    }

    public function programCallbackWithListId(int $customerId, string $triggerId, int $listId)
    {
        $this->programCallback($customerId, $triggerId, null, $listId);
    }

    public function programCallbackCancel(int $customerId, string $triggerId)
    {
        try
        {
            $this->apiClient->delete($this->endPoints->programCallbackUrl($customerId, $triggerId), []);
        }
        catch (Error $ex)
        {
            $this->throwRequestFailedException($ex);
        }
    }

    private function programCallback(int $customerId, string $triggerId, $userId, $listId)
    {
        try
        {
            $this->apiClient->post($this->endPoints->programCallbackUrl($customerId, $triggerId),[
                'user_id' => $userId,
                'list_id' => $listId,
            ]);
        }
        catch (Error $ex)
        {
            $this->throwRequestFailedException($ex);
        }
    }

    private function throwRequestFailedException($ex)
    {
        throw new RequestFailed('Program callback failed: ' . $ex->getMessage(), $ex->getCode(), $ex);
    }
}
