<?php

namespace Suite\Api;

use PHPUnit_Framework_MockObject_MockObject;
use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Client;
use Suite\Api\RequestFailed;
use Suite\Api\Email\EndPoints;
use Suite\Api\Email\Preview;

class PreviewTest extends BaseTestCase
{

    const API_BASE_URL = 'api_base_url';
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;

    /** @var ServicesEndPoints */
    private $endPoints;

    /** @var Client|PHPUnit_Framework_MockObject_MockObject */
    private $apiClient;

    /** @var Preview */
    private $emailPreview;

    /* @var int */
    private $customerId;

    /* @var int */
    private $campaignId;


    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->emailPreview = new Preview($this->apiClient, $this->endPoints);
        $this->customerId = 555;
        $this->campaignId = 123;
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
