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
        return $this->baseUrl($customerId) . "/{$contactListId}/contacts/?limit={$limit}&offset={$offset}";
    }

    public function contactIdsInList(int $customerId, int $contactListId, int $limit = null, int $offset = null)
    {
        $result = $this->baseUrl($customerId) . "/{$contactListId}/contactIds";
        if (null !== $limit && null !== $offset) {
            $result .= "?\$top={$limit}&\$skiptoken={$offset}";
        }
        return $result;
    }

    public function deleteContactsFromList(int $customerId, int $contactListId): string
    {
        return $this->baseUrl($customerId) . "/{$contactListId}/delete";
    }
}
