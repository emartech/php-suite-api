<?php

use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\Segment\EndPoints;
use Suite\Api\Segment\Segment;
use Suite\Api\RequestFailed;

class SegmentTest extends BaseTestCase
{
    const ERROR_CODE = 1234;

    /**
     * @test
     */
    public function onApiErrorExceptionIsThrown()
    {
        $clientMock = $this->mock(Client::class);
        $service = new Segment($clientMock, new EndPoints('base_url'));

        $clientMock->expects($this->once())->method('get')->with('base_url/123456/filter')
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
