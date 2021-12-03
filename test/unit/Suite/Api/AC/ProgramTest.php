<?php

namespace Suite\Api;

use Suite\Api\AC\Program;
use Suite\Api\AC\EndPoints;
use Suite\Api\Test\Helper\TestCase;

class ProgramTest extends TestCase
{
    const TRIGGER_ID = 'trigger_id';
    const USER_ID = 1;
    const LIST_ID = 2;

    /** @var Program */
    private $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->createMock(Client::class);
        $this->program = new Program($this->apiClient, $this->endPoints);
    }

    /**
     * @test
     */
    public function programCallbackWithUserId_Perfect_Perfect()
    {
        $this->expectApiCallSuccess(
            [
                'user_id' => self::USER_ID,
                'list_id' => null,
                'status' => Program::CALLBACK_STATUS_DONE,
            ]
        );

        $this->program->programCallbackWithUserId($this->customerId, self::TRIGGER_ID, self::USER_ID);
    }

    /**
     * @test
     */
    public function programCallbackWithUserId_postThrowsError_ThrowsRequestFailException()
    {
        $this->expectException(RequestFailed::class);
        $this->expectApiCallFailure();

        $this->program->programCallbackWithUserId($this->customerId, self::TRIGGER_ID, self::USER_ID);
    }

    /**
     * @test
     */
    public function programCallbackWithListId_Perfect_Perfect()
    {
        $this->expectApiCallSuccess(
            [
                'user_id' => null,
                'list_id' => self::LIST_ID,
                'status' => Program::CALLBACK_STATUS_DONE
            ]
        );

        $this->program->programCallbackWithListId($this->customerId, self::TRIGGER_ID, self::LIST_ID);
    }

    /**
     * @test
     */
    public function programCallbackWithListId_postThrowsError_ThrowsRequestFailException()
    {
        $this->expectException(RequestFailed::class);
        $this->expectApiCallFailure();

        $this->program->programCallbackWithListId($this->customerId, self::TRIGGER_ID, self::LIST_ID);
    }

    /**
     * @test
     */
    public function programCallbackCancel_Perfect_Perfect()
    {
        $this->expectApiCallSuccess(
            [
                'user_id' => 0,
                'list_id' => null,
                'status' => Program::CALLBACK_STATUS_CANCELED
            ]
        );

        $this->program->programCallbackCancel($this->customerId, self::TRIGGER_ID);
    }

    private function expectApiCallSuccess(array $postParams)
    {
        $this->apiClient->expects($this->once())->method('post')->with(
            $this->endPoints->programCallbackUrl($this->customerId, self::TRIGGER_ID), $postParams
        )->willReturn($this->apiSuccess());
    }

    private function expectApiCallFailure()
    {
        $this->apiClient
            ->expects($this->any())
            ->method('post')
            ->willThrowException(new Error());
    }
}
