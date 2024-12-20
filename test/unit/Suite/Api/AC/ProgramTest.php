<?php

namespace Suite\Api;

use Suite\Api\AC\Program;
use Suite\Api\AC\EndPoints;
use Suite\Api\Test\Helper\TestCase;

class ProgramTest extends TestCase
{
    private const TRIGGER_ID = 'trigger_id';
    private const USER_ID = 1;
    private const LIST_ID = 2;

    private EndPoints $endPoints;
    private Program $program;

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
    public function programCallbackWithUserId_CallEndpointWithCorrectParameters(): void
    {
        $this->expectSuccessfulDoneRequest(
            [
                'user_id' => self::USER_ID,
                'list_id' => null
            ]
        );

        $this->program->programCallbackWithUserId($this->customerId, self::TRIGGER_ID, self::USER_ID);
    }

    /**
     * @test
     */
    public function programCallbackWithUserId_postThrowsError_ThrowsRequestFailException(): void
    {
        $this->expectException(RequestFailed::class);
        $this->expectApiCallFailure();

        $this->program->programCallbackWithUserId($this->customerId, self::TRIGGER_ID, self::USER_ID);
    }

    /**
     * @test
     */
    public function programCallbackWithListId_CallEndpointWithCorrectParameters(): void
    {
        $this->expectSuccessfulDoneRequest(
            [
                'user_id' => null,
                'list_id' => self::LIST_ID
            ]
        );

        $this->program->programCallbackWithListId($this->customerId, self::TRIGGER_ID, self::LIST_ID);
    }

    /**
     * @test
     */
    public function programCallbackWithListId_postThrowsError_ThrowsRequestFailException(): void
    {
        $this->expectException(RequestFailed::class);
        $this->expectApiCallFailure();

        $this->program->programCallbackWithListId($this->customerId, self::TRIGGER_ID, self::LIST_ID);
    }

    /**
     * @test
     */
    public function programBatchCallbackDone_CalledWithCorrectParameters(): void
    {
        $postParams = [
            'this' => 'is',
            'the' => 'callback',
            'post' => 'params',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->endPoints->programBatchCallbackDoneUrl($this->customerId),
                $postParams
            )
            ->willReturn($this->apiSuccess());

        $this->program->programBatchCallbackDone($this->customerId, $postParams);
    }

    /**
     * @test
     */
    public function programCallbackCancel_Perfect_Perfect(): void
    {
        $this->expectSuccessfulCancelRequest();

        $this->program->programCallbackCancel($this->customerId, self::TRIGGER_ID);
    }

    private function expectSuccessfulDoneRequest(array $postParams): void
    {
        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->endPoints->programCallbackDoneUrl($this->customerId, self::TRIGGER_ID),
                $postParams
            )
            ->willReturn($this->apiSuccess());
    }

    private function expectSuccessfulCancelRequest(): void
    {
        $this->apiClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->endPoints->programCallbackCancelUrl($this->customerId, self::TRIGGER_ID),
                [
                    'user_id' => null,
                    'list_id' => null
                ]
            )
            ->willReturn($this->apiSuccess());
    }

    private function expectApiCallFailure(): void
    {
        $this->apiClient
            ->expects($this->any())
            ->method('post')
            ->willThrowException(new Error());
    }
}
