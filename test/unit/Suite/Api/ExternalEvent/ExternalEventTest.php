<?php

use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\ExternalEvent\EndPoints;
use Suite\Api\ExternalEvent\ExternalEvent;
use Suite\Api\RequestFailed;

class ExternalEventTest extends BaseTestCase
{
    const ERROR_CODE = 1234;

    /**
     * @test
     */
    public function onApiErrorExceptionIsThrown()
    {
        $clientMock = $this->mock(Client::class);
        $service = new ExternalEvent($clientMock, new EndPoints('base_url'));

        $clientMock->expects($this->once())->method('get')->with('base_url/123456/event')
            ->will($this->throwException(new Error('error_message', self::ERROR_CODE)));

        try {
            $service->getList(123456);
            $this->fail('An exception was expected');
        } catch (RequestFailed $ex) {
            $this->assertContains('error_message', $ex->getMessage());
            $this->assertEquals(self::ERROR_CODE, $ex->getCode());
        }
    }
}
