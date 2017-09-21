<?php

namespace Suite\Api;

use Suite\Api\Contact\Contact;
use Suite\Api\Contact\EndPoints;
use Suite\Api\Test\Helper\TestCase;

class ContactTest extends TestCase
{

    private $contact;

    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->contact = new Contact($this->apiClient, $this->endPoints);
    }

    /**
     * @test
     */
    public function getData_Perfect_Perfect()
    {
        $contacts = [
            $this->createContact('test@test1.com', 1),
            $this->createContact('test@test2.com', 2),
        ];

        $postData = [
            'keyId' => Contact::FIELD_ID,
            'keyValues' => [1, 2],
            'fields' => [Contact::FIELD_EMAIL]
        ];

        $this->apiClient->expects($this->once())->method('post')
            ->with($this->endPoints->getData($this->customerId), $postData)
            ->will($this->apiSuccess($contacts));

        $responseData = $this->contact->getList($this->customerId, Contact::FIELD_ID, [1, 2], [Contact::FIELD_EMAIL]);
        $this->assertEquals($contacts, $responseData);
    }

    /**
     * @test
     */
    public function getData_ContactNotFound_EmptyArrayReturned()
    {
        $this->apiClient->expects($this->once())->method('post')
            ->will($this->apiSuccess([]));

        $responseData = $this->contact->getList($this->customerId, Contact::FIELD_ID, [], [Contact::FIELD_EMAIL]);
        $this->assertEquals([], $responseData);
    }

    /**
     * @test
     */
    public function get_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure('post');

        try {
            $this->contact->getList($this->customerId, Contact::FIELD_ID, [], [Contact::FIELD_EMAIL]);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }

    private function apiSuccess(array $data = [])
    {
        return $this->returnValue([
            'success' => true,
            'replyCode' => self::API_SUCCESS_CODE,
            'replyText' => self::API_SUCCESS_TEXT,
            'data' => [
                'errors' => [],
                'result' => $data ?: false,
            ],
        ]);
    }

    private function createContact(string $email, int $id)
    {
        return [
            Contact::FIELD_EMAIL => $email,
            Contact::FIELD_ID => $id,
            Contact::FIELD_UID => 'testuid'
        ];
    }
}