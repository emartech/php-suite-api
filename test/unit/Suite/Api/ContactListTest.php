<?php

namespace Suite\Api;

use InvalidArgumentException;
use Suite\Api\Test\Helper\TestCase;

class ContactListTest extends TestCase
{
    private Contactlist $listService;
    private int $contactListId = 654321;
    private string $listName = 'list_name';

    protected function setUp(): void
    {
        parent::setUp();
        $this->endPoints = new ContactListEndPoints(self::API_BASE_URL);
        $this->apiClient = $this->createMock(Client::class);
        $this->listService = new ContactList($this->apiClient, $this->endPoints);
    }

    /**
     * @test
     */
    public function createContactList_Perfect_Perfect(): void
    {
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->method('post')
            ->with(
                "api_base_url/$this->customerId/contactlist",
                [
                    'name' => $this->listName,
                    'key_id' => 'id',
                    'external_ids' => $contactIds,
                ]
            )
            ->willReturn($this->apiSuccess(['id' => $this->contactListId]));

        $contactListId = $this->listService->createContactList($this->customerId, $this->listName, $contactIds);
        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     */
    public function createContactList_CalledWithBusinessAreaId_PassesBusinessAreaId(): void
    {
        $businessAreaId = 'HU';
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                "api_base_url/$this->customerId/contactlist",
                [
                    'name' => $this->listName,
                    'key_id' => 'id',
                    'external_ids' => $contactIds,
                    'business_area_id' => $businessAreaId
                ]
            )
            ->willReturn($this->apiSuccess(['id' => $this->contactListId]));

        $this->listService->createContactList($this->customerId, $this->listName, $contactIds, $businessAreaId);
    }

    /**
     * @test
     */
    public function createContactList_NoContactIdsGiven_ListCreated(): void
    {
        $contactIds = [];
        $this->apiClient
            ->method('post')
            ->willReturn($this->apiSuccess(['id' => $this->contactListId]));

        $contactListId = $this->listService->createContactList($this->customerId, $this->listName, $contactIds);
        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     */
    public function createContactList_ApiCallFails_ExceptionThrown(): void
    {
        $this->apiClient
            ->method('post')
            ->willReturn($this->apiFailure());
        $this->expectException(RequestFailed::class);

        $this->listService->createContactList($this->customerId, $this->listName, []);
    }

    /**
     * @test
     */
    public function createContactList_ContactListNameIsEmptyString_SuiteAPINotCalled(): void
    {
        $this->apiClient->expects($this->never())->method('post');
        $this->expectException(InvalidArgumentException::class);

        $this->listService->createContactList($this->customerId, '       ');
    }

    /**
     * @test
     */
    public function getContactLists_Perfect_Perfect(): void
    {
        $contactLists = [
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
        ];

        $this->apiClient
            ->method('get')
            ->with("api_base_url/$this->customerId/contactlist")
            ->willReturn($this->apiSuccess($contactLists));

        $returnedContactLists = $this->listService->getContactLists($this->customerId);

        $this->assertEquals($contactLists, $returnedContactLists);
    }

    /**
     * @test
     */
    public function findContactListByName_Perfect_Perfect(): void
    {
        $contactLists = [
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
            $this->contactListData($this->contactListId, $this->listName),
        ];

        $this->apiClient
            ->method('get')
            ->with("api_base_url/$this->customerId/contactlist")
            ->willReturn($this->apiSuccess($contactLists));

        $contactListId = $this->listService->findContactListByName($this->customerId, $this->listName);

        $this->assertEquals($this->contactListId, $contactListId);
    }

    /**
     * @test
     * @dataProvider contactListNameProvider
     */
    public function findContactListByName_CasesDoNotMatch_ContactListIdStillReturned($contactListName): void
    {
        $contactLists = [
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
            $this->contactListData($this->contactListId, $contactListName),
        ];

        $this->apiClient
            ->method('get')
            ->willReturn($this->apiSuccess($contactLists));

        $contactListId = $this->listService->findContactListByName($this->customerId, $contactListName);

        $this->assertEquals($this->contactListId, $contactListId);
    }

    public function contactListNameProvider(): array
    {
        return [
            'upperCase' => [strtoupper($this->listName)],
            'lowerCase' => [strtolower($this->listName)],
            'spaceAfter' => [$this->listName . "     "],
            'spaceBefore' => ["     " . $this->listName],
            'spaceInContactListName' => [" my very best contact list "],
        ];
    }

    /**
     * @test
     */
    public function findContactListByName_ListDoesNotExist_ReturnsNull(): void
    {
        $contactLists = [
            $this->contactListData('id1', 'contact list 1'),
            $this->contactListData('id2', 'contact list 2'),
        ];

        $this->apiClient
            ->method('get')
            ->willReturn($this->apiSuccess($contactLists));

        $contactListId = $this->listService->findContactListByName($this->customerId, $this->listName);

        $this->assertNull($contactListId);
    }

    /**
     * @test
     */
    public function addToContactList_Perfect_Perfect(): void
    {
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                "api_base_url/$this->customerId/contactlist/654321/add",
                [
                    'key_id' => 'id',
                    'external_ids' => $contactIds,
                ]
            )->willReturn($this->apiSuccess());

        $this->listService->addToContactList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     */
    public function addToContactList_ApiFailure_ThrowsException(): void
    {
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->method('post')
            ->willReturn($this->apiFailure());
        $this->expectException(RequestFailed::class);

        $this->listService->addToContactList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     */
    public function replaceContactList_Perfect_Perfect(): void
    {
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                "api_base_url/$this->customerId/contactlist/654321/replace",
                [
                    'key_id' => 'id',
                    'external_ids' => $contactIds,
                ]
            )->willReturn($this->apiSuccess());

        $this->listService->replaceContactList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     */
    public function replaceContactList_ApiFailure_ThrowsException(): void
    {
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->method('post')
            ->willReturn($this->apiFailure());
        $this->expectException(RequestFailed::class);

        $this->listService->replaceContactList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     */
    public function getContactIdsInList_CalledWithProperUrl_ApiResponseConverted(): void
    {
        $response = ['value' => [1, 2, 3], 'next' => null];
        $this->apiClient
            ->method('get')
            ->with("api_base_url/$this->customerId/contactlist/$this->contactListId/contactIds")
            ->willReturn($this->apiSuccess($response));

        $result = $this->listService->getContactIdsInList($this->customerId, $this->contactListId);
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     */
    public function getContactIdsInList_CalledWithProperUrlAndParams_ApiResponseConverted(): void
    {
        $response = ['value' => [1, 2, 3], 'next' => null];
        $this->apiClient
            ->method('get')
            ->with("api_base_url/$this->customerId/contactlist/$this->contactListId/contactIds?%24top=1&%24skiptoken=1")
            ->willReturn($this->apiSuccess($response));

        $result = $this->listService->getContactIdsInList($this->customerId, $this->contactListId, 1, 1);
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     */
    public function getContactIdsInList_ApiCallFails_ExceptionThrown(): void
    {
        $this->apiClient->method('get')->will($this->apiFailure());
        $this->expectException(RequestFailed::class);

        $this->listService->getContactIdsInList($this->customerId, $this->contactListId);
    }

    /**
     * @test
     */
    public function getContactIdsByNextUrl_CalledWithProperUrl_ApiResponseConverted(): void
    {
        $nextUrl = "/123/contactlist/456/contactIds";
        $response = ['value' => [1, 2, 3], 'next' => '/next/url'];
        $this->apiClient
            ->method('get')
            ->with($nextUrl)
            ->willReturn($this->apiSuccess($response));

        $result = $this->listService->getContactIdsByNextUrl($nextUrl);
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     */
    public function getContactIdsByNextUrl_ApiCallFails_ExceptionThrown(): void
    {
        $this->apiClient->method('get')->will($this->apiFailure());
        $this->expectException(RequestFailed::class);

        $this->listService->getContactIdsByNextUrl('/test');
    }

    /**
     * @test
     */
    public function getContactsOfList_Perfect_Perfect(): void
    {
        $limit = 100;
        $offset = 200;

        $chunk = [1, 2, 3];
        $this->apiClient
            ->method('get')
            ->with("api_base_url/$this->customerId/contactlist/654321/contacts/?limit=100&offset=200")
            ->willReturn($this->apiSuccess($chunk));

        $result = $this->listService->getContactsOfList($this->customerId, $this->contactListId, $limit, $offset);
        $this->assertEquals($chunk, $result);
    }

    /**
     * @test
     */
    public function getContactsOfList_NoDataInResult_EmptyArrayReturned(): void
    {
        $limit = 100;
        $offset = 200;

        $this->apiClient
            ->method('get')
            ->with($this->endPoints->contactsOfList($this->customerId, $this->contactListId, $limit, $offset))
            ->willReturn($this->apiSuccess());

        $result = $this->listService->getContactsOfList($this->customerId, $this->contactListId, $limit, $offset);
        $this->assertEquals([], $result);
    }

    /**
     * @test
     */
    public function getContactsOfList_ApiCallFails_ExceptionThrown(): void
    {
        $this->apiClient->method('get')->will($this->apiFailure());
        $this->expectException(RequestFailed::class);

        $this->listService->getContactsOfList($this->customerId, $this->contactListId, 100, 200);
    }

    /**
     * @test
     */
    public function getContactListChunkIterator_ListFitsInSingleChunk_ContactIdsReturned(): void
    {
        $chunkSize = 3;
        $this->apiClient->expects($this->once())->method('get')
            ->with("api_base_url/$this->customerId/contactlist/$this->contactListId/contactIds?%24top=3")
            ->willReturn(
                $this->apiSuccess(['value' => [1, 2, 3], 'next' => null])
            );
        $iterator = $this->listService->getListChunkIterator($this->customerId, $this->contactListId, $chunkSize);
        $this->assertEquals([[1, 2, 3]], iterator_to_array($iterator));
    }

    /**
     * @test
     */
    public function getContactListChunkIterator_ChunkSizeNotPassed_TopNotSentInRequest(): void
    {
        $chunkSize = 3;
        $this->apiClient->expects($this->once())->method('get')
            ->with("api_base_url/$this->customerId/contactlist/$this->contactListId/contactIds")
            ->willReturn(
                $this->apiSuccess(['value' => [1, 2, 3], 'next' => null])
            );
        $iterator = $this->listService->getListChunkIterator($this->customerId, $this->contactListId);
        $this->assertEquals([[1, 2, 3]], iterator_to_array($iterator));
    }

    /**
     * @test
     */
    public function deleteContactsFromList_Perfect_Perfect(): void
    {
        $contactIds = [1, 2, 3];
        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                "api_base_url/$this->customerId/contactlist/654321/delete",
                [
                    'key_id' => 'id',
                    'external_ids' => $contactIds,
                ]
            );

        $this->listService->deleteContactsFromList($this->customerId, $this->contactListId, $contactIds);
    }

    /**
     * @test
     */
    public function deleteContactsFromList_postThrowsError_ThrowsRequestFailException(): void
    {
        $this->apiClient->method('post')->willThrowException(new Error());

        $this->expectException(RequestFailed::class);
        $this->listService->deleteContactsFromList($this->customerId, $this->contactListId, []);
    }

    public function contactListData($id, $name): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'created' => '2014-11-05 12:34:56',
            'type' => 0,
        ];
    }

    private function apiFailure(): \PHPUnit\Framework\MockObject\Stub\Exception
    {
        return $this->throwException(new Error(self::API_FAILURE_TEXT, self::API_FAILURE_CODE));
    }
}
