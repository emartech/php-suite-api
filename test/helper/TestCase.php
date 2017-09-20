<?php

namespace Suite\Api\Test\Helper;

use Emartech\TestHelper\BaseTestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Suite\Api\Client;
use Suite\Api\Error;

class TestCase extends BaseTestCase
{
    const API_BASE_URL = 'api_base_url';
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;
    const API_FAILURE_TEXT = 'FAIL';
    const API_FAILURE_CODE = 9999;

    protected $customerId = 555;
    protected $campaignId = 123;

    /** @var EndPoints */
    protected $endPoints;

    /** @var Client|PHPUnit_Framework_MockObject_MockObject */
    protected $apiClient;

    protected function expectApiFailure(string $method = 'get')
    {
        $this->apiClient->expects($this->once())->method($method)
            ->will($this->throwException(new Error()));
    }

    protected function expectApiFailureOnGet()
    {
        $this->apiClient->expects($this->once())->method('post')
            ->will($this->throwException(new Error()));
    }
}