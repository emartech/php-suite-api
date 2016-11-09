<?php

namespace Suite\Api;

use Emartech\TestHelper\BaseTestCase;

use Escher;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_Constraint_IsEqual;
use PHPUnit_Framework_MockObject_Builder_InvocationMocker;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_MockObject_Stub;

class ClientTest extends BaseTestCase
{
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;
    const API_FAILURE_TEXT = 'FAIL';
    const API_FAILURE_CODE = 9999;

    const ESCHER_KEY = 'escher_key';
    const ESCHER_SECRET = 'escher_secret';

    /**
     * @var EscherProvider|PHPUnit_Framework_MockObject_MockObject
     */
    private $escherProvider;

    /**
     * @var Escher|PHPUnit_Framework_MockObject_MockObject
     */
    private $escher;

    /**
     * @var ClientInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClient;

    /**
     * @var Client
     */
    protected $apiClient;

    /**
     * @var string
     */
    private $oldApiProxyHost;


    protected function setUp()
    {
        parent::setUp();
        $this->oldApiProxyHost = getenv("API_PROXY_HOST");
        $this->escherProvider = $this->mock(EscherProvider::class);
        $this->escher = $this->mock(Escher::class);
        $this->guzzleClient = $this->mock(GuzzleClient::class);
        $this->apiClient = new Client($this->dummyLogger, $this->escherProvider, $this->guzzleClient, new SuiteResponseProcessor($this->dummyLogger));
    }

    protected function tearDown()
    {
        putenv("SUITE_API_HOST=".$this->oldApiProxyHost);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getBaseUrl_Perfect_ApiProxyUrlReturned()
    {
        putenv('SUITE_API_HOST=api_proxy_host');
        $customerId = 30;
        $this->assertContains('api_proxy_host', getenv('SUITE_API_HOST')."/api/v2/internal/{$customerId}/contactlist");
    }

    /**
     * @test
     */
    public function responseOfSuccessfulGetRequestShouldIndicateSuccess()
    {
        $this->expectSuccessfulGet('https://url')->will($this->apiSuccess());
        $response = $this->apiClient->get('url');
        $this->assertSuccessful($response);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulGetRequestShouldContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulGet('https://url')->will($this->apiSuccess());
        $response = $this->apiClient->get('url');
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulRequestShouldContainAdditionalDataReceivedFromApi()
    {
        $this->expectSuccessfulGet('https://url')->will($this->apiSuccess(array('data' => 'DATA')));
        $response = $this->apiClient->get('url');
        $this->assertThat($response, $this->structure(array('data' => 'DATA')));
    }

    /**
     * @test
     */
    public function responseOfSuccessfulPostRequestShouldIndicateSuccessAndContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulPost('https://url', array())->will($this->apiSuccess());
        $response = $this->apiClient->post('url', array());
        $this->assertSuccessful($response);
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     * @expectedException \Suite\Api\Error
     * @expectedExceptionMessage FAIL
     */
    public function responseOfBadApiRequestShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectGetYieldingBadResponse('https://url')->will($this->apiFailure());
        $this->apiClient->get('url');
    }

    /**
     * @test
     * @expectedException \Suite\Api\Error
     * @expectedExceptionMessage Could not execute API request.
     */
    public function responseOfUnsuccessfulApiRequestShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectGetYieldingRequestException('https://url');
        $this->apiClient->get('url');
    }

    /**
     * @test
     * @expectedException \Suite\Api\Error
     * @expectedExceptionMessage API response format was wrong
     */
    public function responseContainingInvalidFormatShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectSuccessfulGet('https://url')->will($this->returnValue('NOT A JSON STRING'));
        $this->apiClient->get('url');
    }

    /**
     * @test
     */
    public function requestShouldContainAuthorizationHeaders()
    {
        $this->expectSuccessfulGet('https://url', $this->expectedHeaders());
        $this->apiClient->get('url');
    }

    /**
     * @test
     */
    public function httpsIsUsedInRequestUrl()
    {
        $apiWrapper = new Client($this->dummyLogger, $this->escherProvider, $this->guzzleClient, new SuiteResponseProcessor($this->dummyLogger));
        $this->expectSuccessfulGet('https://url');
        $apiWrapper->get('url');
    }

    /**
     * @param $response
     */
    protected function assertSuccessful($response)
    {
        $this->assertThat($response, $this->structure(array('success' => $this->isTrue())));
    }

    /**
     * @param $response
     */
    protected function assertUnsuccessful($response)
    {
        $this->assertThat($response, $this->structure(array('success' => $this->isFalse())));
    }

    /**
     * @param $response
     * @param $code
     * @param $text
     */
    protected function assertResponseContains($response, $code, $text)
    {
        $this->assertThat($response, $this->structure(array(
            'replyCode' => $code,
            'replyText' => $text,
        )));
    }

    /**
     * @return PHPUnit_Framework_Constraint
     */
    protected function apiHeaders()
    {
        return $this->structure(array(
            'Content-Type' => 'application/json',
        ));
    }

    /**
     * @param $apiUrl
     * @param $expectedParams
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectSuccessfulPost($apiUrl, $expectedParams)
    {
        $response = $this->mock(Response::class);
        $this->expectSuccessfulRequest('post', $response)->with($apiUrl, $this->apiHeaders(), json_encode($expectedParams));
        $response->expects($this->at(0))->method('getBody')->with(true);
        return $response->expects($this->at(1))->method('getBody')->with(true);
    }

    /**
     * @param $apiUrl
     * @param $expectedHeaders
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectSuccessfulGet($apiUrl, $expectedHeaders = null)
    {
        $response = $this->mock(Response::class);
        $this->expectSuccessfulRequest('get', $response)->with($apiUrl, $expectedHeaders ?: $this->apiHeaders());
        return $response->expects($this->any())->method('getBody')->with(true)->will($this->apiSuccess());
    }

    /**
     * @param $apiUrl
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectGetYieldingBadResponse($apiUrl)
    {
        $response = $this->mock(Response::class);
        $this->expectBadResponse('get', $response)->with($apiUrl, $this->apiHeaders());
        return $response->expects($this->once())->method('getBody')->with(true);
    }

    /**
     * @param $apiUrl
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectGetYieldingRequestException($apiUrl)
    {
        return $this->expectRequest('get', $this->throwException(new RequestException()))->with($apiUrl, $this->apiHeaders());
    }

    /**
     * @param $method
     * @param $response
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectSuccessfulRequest($method, $response)
    {
        return $this->expectRequest($method, $this->returnValue($response));
    }

    /**
     * @param $method
     * @param $response
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectBadResponse($method, $response)
    {
        $ex = new BadResponseException();
        $ex->setResponse($response);
        return $this->expectRequest($method, $this->throwException($ex));
    }

    /**
     * @param $method
     * @param PHPUnit_Framework_MockObject_Stub $result
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectRequest($method, $result)
    {
        $this->escherProvider->expects($this->once())->method('createEscher')->will($this->returnValue($this->escher));
        $this->escherProvider->expects($this->any())->method('getEscherKey')->will($this->returnValue(self::ESCHER_KEY));
        $this->escherProvider->expects($this->any())->method('getEscherSecret')->will($this->returnValue(self::ESCHER_SECRET));

        $this->escher->expects($this->once())->method('signRequest')
            ->with(
                self::ESCHER_KEY,
                self::ESCHER_SECRET,
                $this->equalToIgnoringCase($method),
                $this->stringContains('://'),
                $this->isType('string'),
                $this->contentTypeHeader()
            )
            ->will($this->returnValue($this->allHeaders()));
        $request = $this->mock(RequestInterface::class);
        $request->expects($this->once())->method('send')->will($result);
        return $this->guzzleClient->expects($this->once())->method($method)->will($this->returnValue($request));
    }

    protected function apiSuccess($additionalData = array())
    {
        return $this->returnValue(json_encode(array(
                'replyCode' => self::API_SUCCESS_CODE,
                'replyText' => self::API_SUCCESS_TEXT
            ) + $additionalData));
    }

    protected function apiFailure()
    {
        return $this->returnValue(json_encode(array(
            'replyCode' => self::API_FAILURE_CODE,
            'replyText' => self::API_FAILURE_TEXT
        )));
    }

    private function expectedHeaders()
    {
        return $this->structure($this->allHeaders());
    }

    private function contentTypeHeader()
    {
        return array('Content-Type' => 'application/json');
    }

    /**
     * @return array
     */
    protected function allHeaders()
    {
        return $this->contentTypeHeader() + $this->authHeaders();
    }

    /**
     * @return array
     */
    protected function authHeaders()
    {
        return array('X-Dummy-Auth' => 'SIGNATURE');
    }

    /**
     * @param $method
     * @return PHPUnit_Framework_Constraint_IsEqual
     */
    protected function equalToIgnoringCase($method)
    {
        return $this->equalTo($method, 0, 10, false, true);
    }
}
