<?php

namespace Suite\Api\Acceptance;

use Suite\Api\RequestFailed;
use Suite\Api\Test\Helper\AcceptanceBaseTestCase;
use Suite\Api\Test\Helper\ApiStub;

class ContactListChunkIteratorTest extends AcceptanceBaseTestCase
{
    protected int $customerId = 123456;

    /**
     * @test
     */
    public function getListChunkIterator_EmptyContactList_ReturnsSingleEmptyChunk(): void
    {
        $this->assertEquals([[]], $this->getChunksOfContactList(ApiStub::LIST_ID_FOR_EMPTY_LIST));
    }

    /**
     * @test
     */
    public function getListChunkIterator_ContactListContainsSingleChunk_ReturnsContactIds(): void
    {
        $this->assertEquals([[1, 2, 3]], $this->getChunksOfContactList(ApiStub::LIST_ID_FOR_LIST_WITH_SINGLE_CHUNK));
    }


    /**
     * @test
     */
    public function getListChunkIterator_SeveralChunks_ReturnsContactIdsInChunks(): void
    {
        $this->assertEquals([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [10, 11],
        ], $this->getChunksOfContactList(ApiStub::LIST_ID_FOR_LIST_WITH_MULTIPLE_CHUNKS));
    }


    /**
     * @test
     */
    public function getListChunkIterator_ApiRequestFailed_ThrowsException(): void
    {
        $this->expectException(RequestFailed::class);
        $this->getChunksOfContactList(ApiStub::LIST_ID_FOR_WRONG_RESPONSE);
    }

    private function getChunksOfContactList(int $contactListId): array
    {
        return iterator_to_array($this->factory->createContactList()->getListChunkIterator($this->customerId, $contactListId));
    }
}
