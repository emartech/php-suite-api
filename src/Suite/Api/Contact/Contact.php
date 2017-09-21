<?php

namespace Suite\Api\Contact;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\RequestFailed;

class Contact
{
    const FIELD_ID = 'id';
    const FIELD_EMAIL = 3;

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
            return $response['data']['result'];
        }
        catch (Error $ex)
        {
            throw new RequestFailed('Could not get list of contacts: ' . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
