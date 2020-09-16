<?php

namespace Suite\Api;

use Traversable;

class ContactList
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * @var ContactListEndPoints
     */
    private $endPoints;

    public function __construct(Client $apiClient, ContactListEndPoints $endPoints)
    {
        $this->apiClient = $apiClient;
        $this->endPoints = $endPoints;
    }

    public function createContactList(int $customerId, string $name, array $contactIds = [])
    {
        if (!strlen(trim($name))) {
            throw new \InvalidArgumentException("Empty contact list name given");
        }

        $data = ['name' => $name];

        if (!empty($contactIds)) {
            $data['key_id'] = 'id';
            $data['external_ids'] = $contactIds;
        }

        try {
            return $this->apiClient->post($this->endPoints->createContactList($customerId), $data)['data']['id'];
        } catch (Error $error) {
            throw new RequestFailed('Could not create contact list: ' . $error->getMessage(), $error->getCode(), $error);
        }
    }

    public function getContactLists(int $customerId)
    {
        try {
            return $this->apiClient->get($this->endPoints->contactLists($customerId))['data'];
        } catch (Error $error) {
            throw new RequestFailed('Could not fetch contact lists: ' . $error->getMessage(), $error->getCode(), $error);
        }
    }

    public function findContactListByName(int $customerId, string $listName)
    {
        try {
            foreach ($this->getContactLists($customerId) as $contactListData) {
                if (strtolower($contactListData['name']) == strtolower($listName)) {
                    return $contactListData['id'];
                }
            }

            return null;
        } catch (Error $error) {
            throw new RequestFailed('Could not fetch contact lists: ' . $error->getMessage(), $error->getCode(), $error);
        }
    }

    public function addToContactList(int $customerId, int $contactListId, array $contactIds)
    {
        try {
            $this->apiClient->post($this->endPoints->addToContactList($customerId, $contactListId), [
                'key_id'        => 'id',
                'external_ids'  => $contactIds
            ]);
        } catch (Error $error) {
            throw new RequestFailed('Could not add contacts to list: ' . $error->getMessage(), $error->getCode(), $error);
        }
    }

    public function replaceContactList(int $customerId, int $contactListId, array $contactIds)
    {
        try {
            $this->apiClient->post($this->endPoints->replaceContactList($customerId, $contactListId), [
                'key_id'        => 'id',
                'external_ids'  => $contactIds
            ]);
            return $contactListId;
        } catch (Error $error) {
            throw new RequestFailed('Could not add contacts to list: ' . $error->getMessage(), $error->getCode(), $error);
        }

    }

    public function deleteContactsFromList(int $customerId, int $contactListId, array $contactIds)
    {
        try {
            $this->apiClient->post($this->endPoints->deleteContactsFromList($customerId, $contactListId), [
                'key_id'        => 'id',
                'external_ids'  => $contactIds
            ]);
            return $contactListId;
        } catch (Error $error) {
            throw new RequestFailed('Could not delete contacts from list: ' . $error->getMessage(), $error->getCode(), $error);
        }

    }

    public function getContactsOfList(int $customerId, int $contactListId, int $limit, int $offset)
    {
        try {
            $response = $this->apiClient->get($this->endPoints->contactsOfList($customerId, $contactListId, $limit, $offset));
            return !empty($response['data']) ? $response['data'] : [];
        } catch (Error $error) {
            throw new RequestFailed('Could not fetch contact ids: ' . $error->getMessage(), $error->getCode(), $error);
        }
    }


    /**
     * @param int $customerId
     * @param int $contactListId
     * @param int $chunkSize
     * @return Traversable
     */
    public function getListChunkIterator(int $customerId, int $contactListId, int $chunkSize = 10000) : Traversable
    {
        return new ContactListChunkIterator($this, $customerId, $contactListId, $chunkSize);
    }
}
