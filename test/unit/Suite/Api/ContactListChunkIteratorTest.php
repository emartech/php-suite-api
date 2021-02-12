<?php

namespace Suite\Api;

use Suite\Api\Test\Helper\TestCase;

class ContactListChunkIteratorTest extends TestCase
{
    /**
     * @var int
     */
    protected $customerId = 123456;

    /**
     * @var int
     */
    private $contactListId = 654321;

    /**
     * @var int
     */
    private $chunkSize = 3;

    /**
     * @test
     */
    public function iterate_EmptyContactList_Perfect()
    {
        $iter = new ContactListChunkIterator(
            new class implements ContactListChunkFetcher {
                public function getContactsOfList(int $customerId, int $contactListId, int $limit, int $offset)
                {
                    return [];
                }
            }, $this->customerId, $this->contactListId, $this->chunkSize
        );
        $this->assertEquals([], iterator_to_array($iter));
    }


    /**
     * @test
     */
    public function iterate_OneChunk_Perfect()
    {
        $iter = new ContactListChunkIterator(
            new class implements ContactListChunkFetcher {
                private $counter = 0;
                public function getContactsOfList(int $customerId, int $contactListId, int $limit, int $offset)
                {
                    if ($this->counter == 0) {
                        $this->counter++;
                        return [1, 2];
                    }
                    $this->counter++;
                    return [];
                }
            }, $this->customerId, $this->contactListId, $this->chunkSize
        );

        $this->assertEquals([[1, 2]], iterator_to_array($iter));
    }


    /**
     * @test
     */
    public function iterate_SeveralChunks_Perfect()
    {
        $iter = new ContactListChunkIterator(
            new class implements ContactListChunkFetcher {
                private $counter = 0;
                public function getContactsOfList(int $customerId, int $contactListId, int $limit, int $offset)
                {
                    switch ($this->counter) {
                        case 0: $result = [1, 2, 3]; break;
                        case 1: $result = [4, 5, 6]; break;
                        case 2: $result = [7, 8, 9]; break;
                        case 3: $result = [10, 11]; break;
                        default: $result = [];
                    }
                    $this->counter++;
                    return $result;
                }
            }, $this->customerId, $this->contactListId, $this->chunkSize
        );
        $chunks = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [10, 11],
        ];

        $this->assertEquals($chunks, iterator_to_array($iter));
    }


    /**
     * @test
     */
    public function iterate_ApiRequestFailed_ThrowsException()
    {
        $iter = new ContactListChunkIterator(
            new class implements ContactListChunkFetcher {
                public function getContactsOfList(int $customerId, int $contactListId, int $limit, int $offset)
                {
                    throw new RequestFailed();
                }
            }, $this->customerId, $this->contactListId, $this->chunkSize
        );

        $this->expectException(RequestFailed::class);
        iterator_to_array($iter);
    }
}
