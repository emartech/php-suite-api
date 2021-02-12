<?php

namespace Suite\Api;

interface ContactListChunkFetcher
{
    public function getContactsOfList(int $customerId, int $contactListId, int $limit, int $offset);
}
