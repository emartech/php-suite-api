<?php

namespace Suite\Api;

use PHPUnit_Framework_MockObject_MockObject;
use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Client;
use Suite\Api\Email\EndPoints;
use Suite\Api\Email\Launch;

class LaunchTest extends BaseTestCase
{
    const API_BASE_URL = 'api_base_url';
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;

    /** @var ServicesEndPoints */
    private $endPoints;

    /** @var Client|PHPUnit_Framework_MockObject_MockObject */
    private $apiClient;

    /** @var Launch */
    private $emailLaunch;

    /* @var int */
    private $customerId;

    /* @var int */
    private $campaignId;


    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->emailLaunch = new Launch($this->apiClient, $this->endPoints);
        $this->customerId = 555;
        $this->campaignId = 123;
    }


    /**
     * @test
     */
    public function testLaunch_Perfect_Perfect()
    {
        $this->apiClient->expects($this->once())->method('post')
            ->with($this->endPoints->emailLaunch($this->customerId, $this->campaignId))
            ->will($this->apiSuccess());

        $responseData = $this->emailLaunch->launch($this->customerId, $this->campaignId);
        $this->assertNull($responseData);
    }

    private function apiSuccess()
    {
        return $this->returnValue([
            'replyCode' => self::API_SUCCESS_CODE,
            'replyText' => self::API_SUCCESS_TEXT,
            'data' => null
        ]);
    }


    /**
     * @test
     */
    public function launch_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->emailLaunch->launch($this->customerId, $this->campaignId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }

    private function expectApiFailure()
    {
        $this->apiClient->expects($this->once())->method('post')
            ->will($this->throwException(new \Exception()));
    }
}