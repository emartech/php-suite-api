<?php

namespace Suite\Api;

use PHPUnit_Framework_MockObject_MockObject;
use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Client;
use Suite\Api\RequestFailed;
use Suite\Api\Email\EndPoints;
use Suite\Api\Email\Campaign;

class CampaignTest extends BaseTestCase
{

    const API_BASE_URL = 'api_base_url';
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;

    /** @var ServicesEndPoints */
    private $endPoints;

    /** @var Client|PHPUnit_Framework_MockObject_MockObject */
    private $apiClient;

    /** @var Campaign */
    private $emailCampaign;

    /* @var int */
    private $customerId;

    /* @var int */
    private $campaignId;


    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->emailCampaign = new Campaign($this->apiClient, $this->endPoints);
        $this->customerId = 555;
        $this->campaignId = 123;
    }


    /**
     * @test
     */
    public function getById_Perfect_Perfect()
    {
        $this->expectApiCallForCampaign($this->campaignId);
        $responseData = $this->emailCampaign->getById($this->customerId, $this->campaignId);
        $this->assertEquals(array('id' => $this->campaignId), $responseData);
    }


    /**
     * @test
     */
    public function getById_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->emailCampaign->getById($this->customerId, $this->campaignId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    /**
     * @test
     */
    public function getList_Perfect_Perfect()
    {
        $expectedResponseData = [0 => ['id' => $this->campaignId]];
        $this->expectApiCallWithFilter(['event' => 10], $expectedResponseData);
        $responseData = $this->emailCampaign->getList($this->customerId, ['event' => 10]);
        $this->assertEquals($expectedResponseData, $responseData);
    }


    /**
     * @test
     */
    public function getList_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->emailCampaign->getList($this->customerId, []);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    private function expectApiCallForCampaign($id)
    {
        $this->apiClient->expects($this->once())->method('get')
            ->with($this->endPoints->emailCampaign($this->customerId, $this->campaignId))
            ->will($this->apiSuccess(array('id' => $id)));
    }


    private function expectApiCallWithFilter($expectedFilter, $expectedResponseData)
    {
        $this->apiClient->expects($this->once())->method('get')
            ->with($this->endPoints->emailCampaignList($this->customerId, $expectedFilter))
            ->will($this->apiSuccess($expectedResponseData));
    }


    private function expectApiFailure()
    {
        $this->apiClient->expects($this->once())->method('get')
            ->will($this->throwException(new \Exception()));
    }


    protected function apiSuccess($data)
    {
        return $this->returnValue([
            'success' => true,
            'replyCode' => self::API_SUCCESS_CODE,
            'replyText' => self::API_SUCCESS_TEXT,
            'data' => $data
        ]);
    }
}
