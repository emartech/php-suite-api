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
        $this->programCallbackDone($customerId, $triggerId, $userId, null);
    }

    public function programCallbackWithListId(int $customerId, string $triggerId, int $listId)
    {
        $this->programCallbackDone($customerId, $triggerId, null, $listId);
    }

    public function programCallbackCancel(int $customerId, string $triggerId)
    {
        $this->sendRequest(
            $this->endPoints->programCallbackCancelUrl($customerId, $triggerId),
            null,
            null
        );
    }

    private function programCallbackDone(int $customerId, string $triggerId, $userId, $listId)
    {
        $this->sendRequest(
            $this->endPoints->programCallbackDoneUrl($customerId, $triggerId),
            $userId,
            $listId
        );
    }

    private function sendRequest(string $url, $userId, $listId)
    {
        try {
            $this->apiClient->post($url, [
                'user_id' => $userId,
                'list_id' => $listId,
            ]);
        } catch (Error $ex) {
            throw new RequestFailed('Program callback failed: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
