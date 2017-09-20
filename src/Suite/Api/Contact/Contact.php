<?php

namespace Suite\Api\Contact;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class Contact
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

    public function getList(int $customerId, $keyId, array $keyValues, array $fields): array
    {

        $postData = [
            'keyId' => $keyId,
            'keyValues' => $keyValues,
            'fields' => $fields
        ];

        try
        {
            $response = $this->apiClient->post($this->endPoints->getData($customerId), $postData);
            return $response['data']['data'];
        }
        catch (Error $ex)
        {
            throw new RequestFailed('Could not get list of contacts: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
