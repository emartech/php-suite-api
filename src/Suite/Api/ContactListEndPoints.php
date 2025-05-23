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

    private function baseUrl(int $customerId, string $businessAreaId = null)
    {
        return "{$this->apiBaseUrl}/{$customerId}/contactlist"
        . ($businessAreaId ? "?business_area_id=$businessAreaId" : '');
    }

    public function createContactList(int $customerId, string $businessAreaId = null)
    {
        return $this->baseUrl($customerId, $businessAreaId);
    }

    public function contactLists(int $customerId, string $businessAreaId = null)
    {
        return $this->baseUrl($customerId, $businessAreaId);
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

    public function contactIdsInList(int $customerId, int $contactListId, int $top = null, int $skiptoken = null): string
    {
        return QueryStringAppender::appendParamsToUrl(
            $this->baseUrl($customerId) . "/{$contactListId}/contactIds",
            array_filter(
                ['$top' => $top, '$skiptoken' => $skiptoken],
                fn ($value) => $value !== null
            )
        );
    }

    public function contactIdsInListNextChunk(int $customerId, int $contactListId, string $next = null): ?string
    {
        if (null === $next) {
            return null;
        }
        $rawQuery = parse_url($next, PHP_URL_QUERY);
        parse_str($rawQuery, $query);
        return $this->contactIdsInList($customerId, $contactListId, $query['$top'] ?? null, $query['$skiptoken'] ?? null);
    }
    
    public function deleteContactsFromList(int $customerId, int $contactListId): string
    {
        return $this->baseUrl($customerId) . "/{$contactListId}/delete";
    }
}
