<?php

namespace Suite\Api;

use Emartech\TestHelper\BaseTestCase;

use Escher\Escher;
use Escher\Provider as EscherProvider;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_Constraint_IsEqual;
use PHPUnit_Framework_MockObject_Builder_InvocationMocker;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends BaseTestCase
{
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;
    const API_FAILURE_TEXT = 'FAIL';
    const API_FAILURE_CODE = 9999;

    const ESCHER_KEY = 'escher_key';
    const ESCHER_SECRET = 'escher_secret';
    const URL = 'https://url';

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
     * @var RequestFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $requestFactory;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ResponseInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

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
        $this->requestFactory = $this->mock(RequestFactory::class);
        $this->request = $this->mock(RequestInterface::class);
        $this->response = $this->mock(ResponseInterface::class);

        $this->apiClient = new Client($this->dummyLogger, $this->escherProvider, $this->guzzleClient, $this->requestFactory, new SuiteResponseProcessor($this->dummyLogger));
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
        $this->expectSuccessfulGet(self::URL)->will($this->apiSuccess());
        $response = $this->apiClient->get(self::URL);
        $this->assertSuccessful($response);
    }

    /**
     * @test
     */
    public function assembleParametersAsGetQueryParameters()
    {
        $parameters = array(
            'first' => 1,
            'second' => "sec",
            'third' => "th i rd"
        );
        $this->expectSuccessfulGet(self::URL . '?first=1&second=sec&third=th+i+rd')->will($this->apiSuccess());
        $response = $this->apiClient->get(self::URL, $parameters);
        $this->assertSuccessful($response);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulGetRequestShouldContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulGet(self::URL)->will($this->apiSuccess());
        $response = $this->apiClient->get(self::URL);
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulRequestShouldContainAdditionalDataReceivedFromApi()
    {
        $this->expectSuccessfulGet(self::URL)->will($this->apiSuccess(['data' => 'DATA']));
        $response = $this->apiClient->get(self::URL);
        $this->assertThat($response, $this->structure(['data' => 'DATA']));
    }

    /**
     * @test
     */
    public function responseOfSuccessfulPostRequestShouldIndicateSuccessAndContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulPost(self::URL, [])->will($this->apiSuccess());
        $response = $this->apiClient->post(self::URL, []);
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
        $this->expectGetYieldingBadResponse(self::URL)->will($this->apiFailure());
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     * @expectedException \Suite\Api\Error
     * @expectedExceptionMessage Could not execute API request.
     */
    public function responseOfUnsuccessfulApiRequestShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectGetYieldingRequestException(self::URL);
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     * @expectedException \Suite\Api\Error
     * @expectedExceptionMessage API response format was wrong
     */
    public function responseContainingInvalidFormatShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectSuccessfulGet(self::URL)->will($this->returnValue('NOT A JSON STRING'));
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     */
    public function requestShouldContainAuthorizationHeaders()
    {
        $this->expectSuccessfulGet(self::URL, $this->expectedHeaders())->will($this->apiSuccess());
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     */
    public function httpsIsUsedInRequestUrl()
    {
        $apiWrapper = new Client($this->dummyLogger, $this->escherProvider, $this->guzzleClient, $this->requestFactory, new SuiteResponseProcessor($this->dummyLogger));
        $this->expectSuccessfulGet(self::URL)->will($this->apiSuccess());
        $apiWrapper->get(self::URL);
    }

    /**
     * @param $response
     */
    protected function assertSuccessful($response)
    {
        $this->assertThat($response, $this->structure(['success' => $this->isTrue()]));
    }

    /**
     * @param $response
     */
    protected function assertUnsuccessful($response)
    {
        $this->assertThat($response, $this->structure(['success' => $this->isFalse()]));
    }

    /**
     * @param $response
     * @param $code
     * @param $text
     */
    protected function assertResponseContains($response, $code, $text)
    {
        $this->assertThat($response, $this->structure([
            'replyCode' => $code,
            'replyText' => $text,
        ]));
    }

    /**
     * @return PHPUnit_Framework_Constraint
     */
    protected function apiHeaders()
    {
        return $this->structure(['Content-Type' => 'application/json']);
    }

    /**
     * @param $apiUrl
     * @param $expectedParams
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function expectSuccessfulPost($apiUrl, $expectedParams)
    {
        $this->expectSuccessfulRequest('POST', $apiUrl, $this->apiHeaders(), json_encode($expectedParams));
        return $this->response->expects($this->any())->method('getBody');
    }

    /**
     * @param $apiUrl
     * @param $expectedHeaders
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function expectSuccessfulGet($apiUrl, $expectedHeaders = null)
    {
        $this->expectSuccessfulRequest('GET', $apiUrl, $expectedHeaders ?: $this->apiHeaders());
        return $this->response->expects($this->any())->method('getBody');
    }

    /**
     * @param $apiUrl
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function expectGetYieldingBadResponse($apiUrl)
    {
        $this->expectRequest('GET', $apiUrl, $this->apiHeaders())
            ->will($this->throwException(new BadResponseException('Bad response', $this->request, $this->response)));

        return $this->response->expects($this->any())->method('getBody');
    }

    /**
     * @param $apiUrl
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function expectGetYieldingRequestException($apiUrl)
    {
        return $this->expectRequest('GET', $apiUrl, $this->apiHeaders())
            ->will($this->throwException(new RequestException('Request exception', $this->request)));
    }

    /**
     * @param PHPUnit_Framework_Constraint|mixed $method
     * @param PHPUnit_Framework_Constraint|mixed|null $uri
     * @param PHPUnit_Framework_Constraint|mixed|null $headers
     * @param PHPUnit_Framework_Constraint|mixed|null $body
     */
    private function expectSuccessfulRequest($method, $uri = null, $headers = null, $body = null)
    {
        $this->expectRequest($method, $uri, $headers, $body)->will($this->returnValue($this->response));
    }

    /**
     * @param PHPUnit_Framework_Constraint|mixed $method
     * @param PHPUnit_Framework_Constraint|mixed $uri
     * @param PHPUnit_Framework_Constraint|mixed $headers
     * @param PHPUnit_Framework_Constraint|mixed $body
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function expectRequest(string $method, $uri, $headers, $body = null)
    {
        $this->expectEscherSigning($method);
        $this->request->expects($this->any())->method('getMethod')->will($this->returnValue($method));
        $this->request->expects($this->any())->method('getUri')->will($this->returnValue('mocked URI'));
        $this->requestFactory->expects($this->once())->method('createRequest')
            ->with(
                $method,
                null === $uri ? $this->anything() : $uri,
                null === $headers ? $this->anything() : $headers,
                null === $body ? $this->anything() : $body
            )->will($this->returnValue($this->request));
        return $this->guzzleClient->expects($this->once())->method('send')->with($this->request);
    }

    private function apiSuccess($additionalData = [])
    {
        return $this->returnValue(json_encode([
                'replyCode' => self::API_SUCCESS_CODE,
                'replyText' => self::API_SUCCESS_TEXT
            ] + $additionalData));
    }

    private function apiFailure()
    {
        return $this->returnValue(json_encode([
            'replyCode' => self::API_FAILURE_CODE,
            'replyText' => self::API_FAILURE_TEXT
        ]));
    }

    private function expectedHeaders()
    {
        return $this->structure($this->allHeaders());
    }

    private function contentTypeHeader()
    {
        return ['Content-Type' => 'application/json'];
    }

    /**
     * @return array
     */
    private function allHeaders()
    {
        return $this->contentTypeHeader() + $this->authHeaders();
    }

    /**
     * @return array
     */
    private function authHeaders()
    {
        return ['X-Dummy-Auth' => 'SIGNATURE'];
    }

    /**
     * @param $method
     * @return PHPUnit_Framework_Constraint_IsEqual
     */
    private function equalToIgnoringCase($method)
    {
        return $this->equalTo($method, 0, 10, false, true);
    }

    /**
     * @param $method
     */
    private function expectEscherSigning($method)
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
    }

}
