<?php

namespace Suite\Api;

use Suite\Api\Email\EndPoints;
use Suite\Api\Email\Campaign;
use Suite\Api\Test\Helper\TestCase;

class CampaignTest extends TestCase
{
    /** @var Campaign */
    private $emailCampaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->createMock(Client::class);
        $this->emailCampaign = new Campaign($this->apiClient, $this->endPoints);
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
    public function getList_Filter_Perfect()
    {
        $expectedResponseData = [0 => ['id' => $this->campaignId]];
        $this->expectApiCallWithFilter(['event' => 10], $expectedResponseData);
        $responseData = $this->emailCampaign->getList($this->customerId, ['event' => 10]);
        $this->assertEquals($expectedResponseData, $responseData);
    }


    /**
     * @test
     */
    public function getList_NoFilter_Perfect()
    {
        $expectedResponseData = [0 => ['id' => $this->campaignId]];
        $this->expectApiCallWithoutFilter($expectedResponseData);
        $responseData = $this->emailCampaign->getList($this->customerId);
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

    /**
     * @test
     */
    public function deleteById_CampaignFound_CampaignDeleted()
    {
        $this->expectApiCallForCampaignDelete($this->campaignId);
        $responseData = $this->emailCampaign->deleteById($this->customerId, $this->campaignId);
        $this->assertSame(null, $responseData);
    }

    /**
     * @test
     */
    public function deleteById_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure('post');

        try {
            $this->emailCampaign->deleteById($this->customerId, 1);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    private function expectApiCallForCampaign($id)
    {
        $this->apiClient->expects($this->once())->method('get')
            ->with($this->endPoints->emailCampaign($this->customerId, $this->campaignId))
            ->willReturn($this->apiSuccess(array('id' => $id)));
    }


    private function expectApiCallWithFilter($expectedFilter, $expectedResponseData)
    {
        $this->apiClient->expects($this->once())->method('get')
            ->with($this->endPoints->emailCampaignList($this->customerId), $expectedFilter)
            ->willReturn($this->apiSuccess($expectedResponseData));
    }


    private function expectApiCallWithoutFilter($expectedResponseData)
    {
        $this->apiClient->expects($this->once())->method('get')
            ->with($this->endPoints->emailCampaignList($this->customerId))
            ->willReturn($this->apiSuccess($expectedResponseData));
    }

    private function expectApiCallForCampaignDelete($id)
    {
        $this->apiClient->expects($this->once())->method('post')
            ->with($this->endPoints->emailCampaignDelete($this->customerId), ['emailId' => $id])
            ->willReturn($this->apiSuccess(null));
    }
}
