<?php

namespace Suite\Api;

use Emartech\TestHelper\BaseTestCase;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Message\Request;

class DesResponseProcessorTest extends BaseTestCase
{
    /**
     * @test
     */
    public function processResponse_ValidResponse_ArrayReturned()
    {
        $response = new Response(200, [], '{"test": "passed"}');
        $request = new Request('GET', '');
        $subject = new DesResponseProcessor($this->dummyLogger);
        $this->assertEquals(array('test' => 'passed'), $subject->processResponse($request, $response));
    }

    /**
     * @test
     * @expectedException Error
     */
    public function processResponse_InvalidResponse_ExcpetionThrown()
    {
        $response = new Response(200, [], 'test failed');
        $request = new Request('GET', '');
        $subject = new DesResponseProcessor($this->dummyLogger);
        $this->assertEquals(array('test' => 'passed'), $subject->processResponse($request, $response));
    }
}

