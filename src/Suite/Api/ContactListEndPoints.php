<?php

namespace Suite\Api;

class ContactListEndPoints
{
    /**
     * @var string
     */
    private $apiBaseUrl;

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    private function baseUrl(int $customerId)
    {
        return "{$this->apiBaseUrl}/{$customerId}/contactlist";
    }

    public function createContactList(int $customerId)
    {
        return $this->baseUrl($customerId);
    }

    public function contactLists(int $customerId)
    {
        return $this->baseUrl($customerId);
    }

    public function addToContactList(int $customerId, int $contactListId)
    {
        return $this->baseUrl($customerId) . "/{$contactListId}/add";
    }

    public function replaceContactList(int $customerId, int $contactListId)
    {
        return $this->baseUrl($customerId) . "/{$contactListId}/replace";
    }

    public function contactsOfList(int $customerId, int $contactListId, int $limit, int $offset)
    {
        return $this->baseUrl($customerId) . "/contactlists/{$contactListId}?limit={$limit}&offset={$offset}";
    }
}
