<?php

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Suite\Api\Middleware\Retry;

class RetryTest extends TestCase
{
    const DEFAULT_RETRY_COUNT      = 3;

    const EXCEEDED_RETRY_COUNT     = 3;
    const NOT_EXCEEDED_RETRY_COUNT = 1;
    /**
     * @var Request|MockObject
     */
    private $request;

    /**
     * @var callable
     */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = (new Retry(new NullLogger(), self::DEFAULT_RETRY_COUNT))->createHandler();
        $this->request = $this->createMock(Request::class);
    }

    /**
     * @test
     */
    public function createHandler_returnsFunction()
    {
        $this->assertTrue(is_callable(($this->handler)));
    }

    /**
     * @test
     */
    public function createHandler_returnsFalseWhenNoRetriesLeft()
    {
        $this->assertFalse(
            ($this->handler)(
                self::EXCEEDED_RETRY_COUNT,
                $this->request
            )
        );
    }

    /**
     * @test
     */
    public function createHandler_returnsTrueWhenRetriesLeft()
    {
        $this->assertTrue(
            ($this->handler)(
                self::NOT_EXCEEDED_RETRY_COUNT,
                $this->request,
                new Response(500)
            )
        );
    }

    /**
     * @test
     */
    public function createHandler_returnsFalseFor200()
    {
        $this->assertFalse(
            ($this->handler)(
                self::NOT_EXCEEDED_RETRY_COUNT,
                $this->request,
                new Response(200)
            )
        );
    }

    /**
     * @test
     */
    public function createHandler_returnsTrueWhenServerErrorOccurred()
    {
        $this->assertTrue(
            ($this->handler)(
                self::NOT_EXCEEDED_RETRY_COUNT,
                $this->request,
                new Response(500)
            )
        );
    }

    /**
     * @test
     */
    public function createHandler_returnsTrueWhenConnectionErrorOccurred()
    {
        $this->assertTrue(
            ($this->handler)(
                self::NOT_EXCEEDED_RETRY_COUNT,
                $this->request,
                new Response(200),
                new ConnectException('', $this->request)
            )
        );
    }
}
