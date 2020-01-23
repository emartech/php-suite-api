<?php

namespace Suite\Api;

use Suite\Api\Email\EndPoints;
use Suite\Api\Email\Launch;
use Suite\Api\Test\Helper\TestCase;

class LaunchTest extends TestCase
{
    /** @var Launch */
    private $emailLaunch;

    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->emailLaunch = new Launch($this->apiClient, $this->endPoints);
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

    protected function apiSuccess($data = [])
    {
        return parent::apiSuccess(null);
    }


    /**
     * @test
     */
    public function launch_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure('post');

        try {
            $this->emailLaunch->launch($this->customerId, $this->campaignId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }
}
