<?php

namespace Suite\Api;

use PHPUnit_Framework_MockObject_MockObject;

use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Test\Helper\TestCase;

class ContactListTest extends TestCase
{
    /**
     * @var Contactlist
     */
    private $listService;

    /**
     * @var int
     */
    private $contactListId = 654321;

    /**
     * @var string
     */
    private $listName = 'list_name';

    protected function setUp()
    {
        parent::setUp();
        $this->endPoints = new ContactListEndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->listService = new ContactList($this->apiClient, $this->endPoints);
    }

    /**
     * @test
     */
    public function createContactList_Perfect_Perfect()
    {
        $contactIds = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('post')
            ->with($this->endPoints->createContactList($this->customerId), [
                'name'          => $this->listName,
                'key_id'        => 'id',
                'external_ids'  => $contactIds,
            ])
            ->will($this->apiSuccess(['id' => $this->contactListId]));

        $contactListId = $this->listService->createContactList($this->customerId, $this->listName, $contactIds);
        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     */
    public function createContactList_NoContactIdsGiven_ListCreated()
    {
        $contactIds = [];
        $this->apiClient->expects($this->once())->method('post')
            ->with($this->endPoints->createContactList($this->customerId), [
                'name' => $this->listName,
            ])
            ->will($this->apiSuccess(['id' => $this->contactListId]));

        $contactListId = $this->listService->createContactList($this->customerId, $this->listName, $contactIds);
        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     * @expectedException \Suite\Api\RequestFailed
     */
    public function createContactList_ApiCallFails_ExceptionThrown()
    {
        $this->apiClient->expects($this->once())->method('post')->will($this->apiFailure());

        $this->listService->createContactList($this->customerId, $this->listName, []);
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createContactList_ContactListNameIsEmptyString_SuiteAPINotCalled()
    {
        $this->apiClient->expects($this->never())->method('post');

        $this->listService->createContactList($this->customerId, '       ');
    }


    /**
     * @test
     */
    public function findContactListByName_Perfect_Perfect()
    {
        $contactLists = array(
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
            $this->contactListData($this->contactListId, $this->listName),
        );

        $this->apiClient->expects($this->once())->method('get')->with($this->endPoints->contactLists($this->customerId))
            ->will($this->apiSuccess($contactLists));

        $contactListId = $this->listService->findContactListByName($this->customerId, $this->listName);

        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     */
    public function findContactListByName_CasesDoNotMatch_ContactListIdStillReturned()
    {
        $contactLists = array(
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
            $this->contactListData($this->contactListId, strtoupper($this->listName)),
        );

        $this->apiClient->expects($this->once())->method('get')->with($this->endPoints->contactLists($this->customerId))
            ->will($this->apiSuccess($contactLists));

        $contactListId = $this->listService->findContactListByName($this->customerId, $this->listName);

        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     */
    public function findContactListByName_ListDoesNotExist_ReturnsNull()
    {
        $contactLists = array(
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
        );

        $this->apiClient->expects($this->once())->method('get')->with($this->endPoints->contactLists($this->customerId))
            ->will($this->apiSuccess($contactLists));

        $contactListId = $this->listService->findContactListByName($this->customerId, $this->listName);

        $this->assertNull($contactListId);
    }

    /**
     * @test
     */
    public function addToContactList_Perfect_Perfect()
    {
        $contactIds = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('post')->with($this->endPoints->addToContactList($this->customerId, $this->contactListId), [
            'key_id'        => 'id',
            'external_ids'  => $contactIds
        ])->will($this->apiSuccess());

        $this->listService->addToContactList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     * @expectedException \Suite\Api\RequestFailed
     */
    public function addToContactList_ApiFailure_ThrowsException()
    {
        $contactIds = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('post')->with($this->endPoints->addToContactList($this->customerId, $this->contactListId), [
            'key_id'        => 'id',
            'external_ids'  => $contactIds
        ])->will($this->apiFailure());

        $this->listService->addToContactList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     */
    public function replaceContactList_Perfect_Perfect()
    {
        $contactIds = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('post')->with($this->endPoints->replaceContactList($this->customerId, $this->contactListId), [
            'key_id'        => 'id',
            'external_ids'  => $contactIds
        ])->will($this->apiSuccess());

        $contactListId = $this->listService->replaceContactList($this->customerId, $this->contactListId, $contactIds);
        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     * @expectedException \Suite\Api\RequestFailed
     */
    public function replaceContactList_ApiFailure_ThrowsException()
    {
        $contactIds = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('post')->with($this->endPoints->replaceContactList($this->customerId, $this->contactListId), [
            'key_id'        => 'id',
            'external_ids'  => $contactIds
        ])->will($this->apiFailure());

        $this->listService->replaceContactList($this->customerId, $this->contactListId, $contactIds);
    }


    /**
     * @test
     */
    public function getContactsOfList_Perfect_Perfect()
    {
        $limit = 100;
        $offset = 200;

        $chunk = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('get')->with(
            $this->endPoints->contactsOfList($this->customerId, $this->contactListId, $limit, $offset)
        )->will($this->apiSuccess($chunk));

        $result = $this->listService->getContactsOfList($this->customerId, $this->contactListId, $limit, $offset);
        $this->assertEquals($chunk, $result);
    }


    /**
     * @test
     */
    public function getContactsOfList_NoDataInResult_EmptyArrayReturned()
    {
        $limit = 100;
        $offset = 200;

        $this->apiClient->expects($this->once())->method('get')->with(
            $this->endPoints->contactsOfList($this->customerId, $this->contactListId, $limit, $offset)
        )->will($this->apiSuccess());

        $result = $this->listService->getContactsOfList($this->customerId, $this->contactListId, $limit, $offset);
        $this->assertEquals([], $result);
    }


    /**
     * @test
     * @expectedException \Suite\Api\RequestFailed
     */
    public function getContactsOfList_ApiCallFails_ExceptionThrown()
    {
        $this->apiClient->expects($this->once())->method('get')->will($this->apiFailure());

        $this->listService->getContactsOfList($this->customerId, $this->contactListId, 100, 200);
    }


    /**
     * @test
     */
    public function getContactListChunkIterator_Perfect_Perfect()
    {
        $chunkSize = 3;
        $iterator = $this->listService->getListChunkIterator($this->customerId, $this->contactListId, $chunkSize);
        $this->assertInstanceOf(\Traversable::class, $iterator);

        $this->apiClient->expects($this->at(0))->method('get')->with(
            $this->endPoints->contactsOfList($this->customerId, $this->contactListId, $chunkSize, 0)
        )->will($this->apiSuccess([1, 2, 3]));

        $this->apiClient->expects($this->at(1))->method('get')->with(
            $this->endPoints->contactsOfList($this->customerId, $this->contactListId, $chunkSize, $chunkSize)
        )->will($this->apiSuccess());

        $this->assertEquals([[1, 2, 3]], iterator_to_array($iterator));
    }


    /**
     * @test
     */
    public function deleteContactsFromList_Perfect_Perfect()
    {
        $contactIds = [1, 2, 3];
        $this->apiClient->expects($this->once())->method('post')->with(
            $this->endPoints->deleteContactsFromList($this->customerId, $this->contactListId), ([
                'key_id' => 'id',
                'external_ids' => $contactIds
            ])
        );

        $this->listService->deleteContactsFromList($this->customerId, $this->contactListId, $contactIds);
    }


    /**
     * @test
     */
    public function deleteContactsFromList_postThrowsError_ThrowsRequestFailException()
    {
        $this->apiClient->expects($this->once())->method('post')->willThrowException(new Error());

        $this->assertExceptionThrown(RequestFailed::class, function () {
            $this->listService->deleteContactsFromList($this->customerId, $this->contactListId, []);
        });
    }

    public function contactListData($id, $name)
    {
        return array(
            'id' => $id,
            'name' => $name,
            'created' => '2014-11-05 12:34:56',
            'type' => 0
        );
    }

    private function apiFailure()
    {
        return $this->throwException(new Error(self::API_FAILURE_TEXT, self::API_FAILURE_CODE));
    }
}
