<?php

namespace Suite\Api\AC;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class Program
{
    const CALLBACK_STATUS_DONE = 'done';
    const CALLBACK_STATUS_CANCELED = 'canceled';

    /* @var Client */
    private $apiClient;

    /* @var EndPoints */
    private $endPoints;


    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }

    private function programCallback(int $customerId, string $triggerId, $userId, $listId, string $status = self::CALLBACK_STATUS_DONE)
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
        $this->programCallback($customerId, $triggerId, null, null, Program::CALLBACK_STATUS_CANCELED);
    }
}
