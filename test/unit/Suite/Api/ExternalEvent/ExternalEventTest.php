<?php

use PHPUnit\Framework\TestCase;
use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\ExternalEvent\EndPoints;
use Suite\Api\ExternalEvent\ExternalEvent;
use Suite\Api\RequestFailed;

class ExternalEventTest extends TestCase
{
    const ERROR_CODE = 1234;

    /**
     * @test
     */
    public function onApiErrorExceptionIsThrown()
    {
        $clientMock = $this->createMock(Client::class);
        $service = new ExternalEvent($clientMock, new EndPoints('base_url'));

        $clientMock->expects($this->once())->method('get')->with('base_url/123456/event')
            ->willReturn($this->throwException(new Error('error_message', self::ERROR_CODE)));

        try {
            $service->getList(123456);
            $this->fail('An exception was expected');
        } catch (RequestFailed $ex) {
            $this->assertStringContainsString('error_message', $ex->getMessage());
            $this->assertEquals(self::ERROR_CODE, $ex->getCode());
        }
    }
}
