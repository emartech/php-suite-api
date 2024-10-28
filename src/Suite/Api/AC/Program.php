<?php

namespace Suite\Api\AC;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class Program
{
    private Client $apiClient;
    private EndPoints $endPoints;

    public function __construct(Client $apiClient, EndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }

    public function programCallbackWithUserId(int $customerId, string $triggerId, int $userId): void
    {
        $this->sendRequest(
            $this->endPoints->programCallbackDoneUrl($customerId, $triggerId),
            $this->createPostData($userId, null)
        );
    }

    public function programCallbackWithListId(int $customerId, string $triggerId, int $listId): void
    {
        $this->sendRequest(
            $this->endPoints->programCallbackDoneUrl($customerId, $triggerId),
            $this->createPostData(null, $listId)
        );
    }

    public function programCallbackCancel(int $customerId, string $triggerId): void
    {
        $this->sendRequest(
            $this->endPoints->programCallbackCancelUrl($customerId, $triggerId),
            $this->createPostData(null, null)
        );
    }

    /**
     * Example for single-user use-case:
     *  [
     *      ['trigger_id' => 'a', 'user_id' => 1],
     *      ['trigger_id' => 'b', 'user_id' => 2]
     *  ]
     *
     * Example for user-list use-case:
     *  [
     *      ['trigger_id' => 'a', 'list_id' => 1],
     *      ['trigger_id' => 'b', 'list_id' => 2]
     *  ]
     *
     * Example for mixed use-case 1 (the not used participant can be omitted):
     *  [
     *      ['trigger_id' => 'a', 'list_id' => 1],
     *      ['trigger_id' => 'b', 'user_id' => 1]
     *  ]
     *
     * Example for mixed use-case 2:
     *  [
     *      ['trigger_id' => 'a', 'user_id' => 0, 'list_id' => 1],
     *      ['trigger_id' => 'b', 'user_id' => 1, 'list_id' => 0]
     *  ]
     */
    public function programBatchCallbackDone(int $customerId, array $triggers): void
    {
        $this->sendRequest(
            $this->endPoints->programBatchCallbackDoneUrl($customerId),
            $triggers
        );
    }

    private function sendRequest(string $url, $postData): void
    {
        try {
            $this->apiClient->post($url, $postData);
        } catch (Error $ex) {
            throw new RequestFailed('Program callback failed: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    private function createPostData($userId, $listId): array
    {
        return [
            'user_id' => $userId,
            'list_id' => $listId,
        ];
    }
}
