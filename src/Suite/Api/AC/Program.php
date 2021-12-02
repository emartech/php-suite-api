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

    private function programCallback(int $customerId, string $triggerId, $userId, $listId, string $status)
    {
        try
        {
            $this->apiClient->post($this->endPoints->programCallbackUrl($customerId, $triggerId),[
                'user_id' => $userId,
                'list_id' => $listId,
                'status' => $status,
            ]);
        }
        catch (Error $ex)
        {
            throw new RequestFailed('Program callback failed: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function programCallbackWithUserId(int $customerId, string $triggerId, int $userId, string $status = 'done')
    {
        $this->programCallback($customerId, $triggerId, $userId, null, $status);
    }

    public function programCallbackWithListId(int $customerId, string $triggerId, int $listId, string $status = 'done')
    {
        $this->programCallback($customerId, $triggerId, null, $listId, $status);
    }
}
