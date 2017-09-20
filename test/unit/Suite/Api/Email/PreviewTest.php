<?php

namespace Suite\Api;

use Suite\Api\Email\EndPoints;
use Suite\Api\Email\Preview;
use Suite\Api\Test\Helper\TestCase;

class PreviewTest extends TestCase
{
    /** @var Preview */
    private $emailPreview;

    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->emailPreview = new Preview($this->apiClient, $this->endPoints);
    }


    /**
     * @test
     */
    public function getText_Perfect_Perfect()
    {
        $this->expectApiCallForVersion('text');
        $responseData = $this->emailPreview->getText($this->customerId, $this->campaignId);
        $this->assertEquals($this->getEmailBody(), $responseData);
    }


    /**
     * @test
     */
    public function getHtml_Perfect_Perfect()
    {
        $this->expectApiCallForVersion('html');
        $responseData = $this->emailPreview->getHtml($this->customerId, $this->campaignId);
        $this->assertEquals($this->getEmailBody(), $responseData);
    }


    /**
     * @test
     */
    public function getMobile_Perfect_Perfect()
    {
        $this->expectApiCallForVersion('mobile');
        $responseData = $this->emailPreview->getMobile($this->customerId, $this->campaignId);
        $this->assertEquals($this->getEmailBody(), $responseData);
    }


    /**
     * @test
     */
    public function getText_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->emailPreview->getText($this->customerId, $this->campaignId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    /**
     * @test
     */
    public function getHtml_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->emailPreview->getHtml($this->customerId, $this->campaignId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    /**
     * @test
     */
    public function getMobile_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->emailPreview->getMobile($this->customerId, $this->campaignId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    private function getEmailBody()
    {
        return 'test body';
    }


    private function expectApiCallForVersion($version)
    {
        $this->apiClient->expects($this->once())->method('post')
            ->with($this->endPoints->emailPreview($this->customerId, $this->campaignId), ['version' => $version])
            ->will($this->apiSuccess($this->getEmailBody()));
    }


    private function expectApiFailure()
    {
        $this->apiClient->expects($this->once())->method('post')
            ->will($this->throwException(new \Exception()));
    }


    private function apiSuccess($data)
    {
        return $this->returnValue([
            'success' => true,
            'replyCode' => self::API_SUCCESS_CODE,
            'replyText' => self::API_SUCCESS_TEXT,
            'data' => $data
        ]);
    }
}
