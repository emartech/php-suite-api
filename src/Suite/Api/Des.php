<?php

namespace Suite\Api;

class Des
{
    private $apiClient;
    private $desEndPoints;

    public function __construct(Client $apiClient, DesEndPoints $desEndPoints)
    {
        $this->apiClient = $apiClient;
        $this->desEndPoints = $desEndPoints;
    }


    public function getDesOfCustomer(int $customerId): array
    {
        try {
            $desResult = $this->apiClient->get($this->desEndPoints->desScore($customerId));
        } catch (Error $error) {
            throw new RequestFailed('Could not fetch DES results: ' . $error->getMessage(), $error->getCode(), $error);
        }

        $ret = json_decode($desResult, true);
        return $ret ? $ret : array();
    }
}
