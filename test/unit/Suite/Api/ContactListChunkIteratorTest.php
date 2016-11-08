<?php

namespace Suite\Api;

use Emartech\TestHelper\BaseTestCase;

use PHPUnit_Framework_MockObject_Builder_InvocationMocker;
use PHPUnit_Framework_MockObject_Matcher_Invocation;

class ContactListChunkIteratorTest extends BaseTestCase
{
    /**
     * @var ContactList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $listService;

    /**
     * @var ContactListChunkIterator
     */
    private $iter;

    /**
     * @var int
     */
    private $customerId = 123456;

    /**
     * @var int
     */
    private $contactListId = 654321;

    /**
     * @var int
     */
    private $chunkSize = 3;


    protected function setUp()
    {
        parent::setUp();
        $this->listService = $this->mock(ContactList::class);
        $this->iter = new ContactListChunkIterator(
            $this->listService, $this->customerId, $this->contactListId, $this->chunkSize
        );
    }


    /**
     * @test
     */
    public function iterate_EmptyContactList_Perfect()
    {
        $this->expectChunkLoad($this->at(0), 0)->will($this->returnValue([]));

        $this->assertEquals([], iterator_to_array($this->iter));
    }


    /**
     * @test
     */
    public function iterate_OneChunk_Perfect()
    {
        $theChunk = [1, 2];

        $this->expectChunkLoad($this->at(0), 0)->will($this->returnValue($theChunk));
        $this->expectChunkLoad($this->at(1), 3)->will($this->returnValue([]));

        $this->assertEquals([$theChunk], iterator_to_array($this->iter));
    }


    /**
     * @test
     */
    public function iterate_SeveralChunks_Perfect()
    {
        $chunks = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [10, 11],
        ];

        $this->expectChunkLoad($this->at(0), 0)->will($this->returnValue($chunks[0]));
        $this->expectChunkLoad($this->at(1), 3)->will($this->returnValue($chunks[1]));
        $this->expectChunkLoad($this->at(2), 6)->will($this->returnValue($chunks[2]));
        $this->expectChunkLoad($this->at(3), 9)->will($this->returnValue($chunks[3]));
        $this->expectChunkLoad($this->at(4), 12)->will($this->returnValue([]));

        $this->assertEquals($chunks, iterator_to_array($this->iter));
    }


    /**
     * @test
     * @expectedException \Suite\Api\RequestFailed
     */
    public function iterate_ApiRequestFailed_ThrowsException()
    {
        $this->expectChunkLoad($this->at(0), 0)->will($this->throwException(new RequestFailed()));

        iterator_to_array($this->iter);
    }


    /**
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     * @param int $offset
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function expectChunkLoad(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher, $offset) : PHPUnit_Framework_MockObject_Builder_InvocationMocker
    {
        return $this->listService->expects($matcher)->method('getContactsOfList')
            ->with($this->customerId, $this->contactListId, $this->chunkSize, $offset);
    }
}