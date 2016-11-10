<?php

namespace Suite\Api;

use PHPUnit_Framework_MockObject_MockObject;

use Emartech\TestHelper\BaseTestCase;

class DesTest extends BaseTestCase
{
    const API_BASE_URL = 'api_base_url';
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;
    const API_FAILURE_TEXT = 'FAIL';
    const API_FAILURE_CODE = 9999;

    private $subject;
    private $customerId = 123456;

    protected function setUp()
    {
        parent::setUp();
        $this->apiClient = $this->mock(Client::class);
        $this->subject = new Des($this->apiClient, new DesEndPoints(self::API_BASE_URL));
        $this->desEndPoint = self::API_BASE_URL.'/score/'.$this->customerId;
    }

    /**
     * @test
     * @expectedException \Suite\Api\RequestFailed
     */
    public function getDesOfCustomer_ApiCallFails_ExceptionThrown()
    {
        $this->apiClient->expects($this->once())->method('get')->will($this->apiFailure());
        $this->subject->getDesOfCustomer($this->customerId);
    }

    /**
     * @test
     */
    public function getDesOfCustomer_ApiSuccess_DataReturned()
    {
        $this->apiClient->expects($this->once())->method('get')->with($this->desEndPoint)
            ->will($this->returnValue('{"test": "passed"}'));

        $this->assertEquals(array("test" => "passed"), $this->subject->getDesOfCustomer($this->customerId));
    }


    private function apiFailure()
    {
        return $this->throwException(new Error(self::API_FAILURE_TEXT, self::API_FAILURE_CODE));
    }
}
