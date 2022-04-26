<?php

namespace Suite\Api;

use Escher\Escher;
use Escher\Provider as EscherProvider;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use Suite\Api\Test\Helper\TestCase;

class ClientTest extends TestCase
{
    const ESCHER_KEY = 'escher_key';
    const ESCHER_SECRET = 'escher_secret';
    const URL = 'https://url';

    /**
     * @var EscherProvider|MockObject
     */
    private $escherProvider;

    /**
     * @var Escher|MockObject
     */
    private $escher;

    /**
     * @var ClientInterface|MockObject
     */
    private $guzzleClient;

    /**
     * @var RequestFactory|MockObject
     */
    private $requestFactory;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var string
     */
    private $oldApiProxyHost;


    protected function setUp(): void
    {
        parent::setUp();

        $this->oldApiProxyHost = getenv("API_PROXY_HOST");
        $this->escherProvider = $this->createMock(EscherProvider::class);
        $this->escher = $this->createMock(Escher::class);
        $this->guzzleClient = $this->createMock(GuzzleClient::class);
        $this->requestFactory = $this->createMock(RequestFactory::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->apiClient = new Client(
            new NullLogger(),
            $this->escherProvider,
            $this->guzzleClient,
            $this->requestFactory,
            new SuiteResponseProcessor(new NullLogger())
        );
    }

    protected function tearDown(): void
    {
        putenv("SUITE_API_HOST=" . $this->oldApiProxyHost);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getBaseUrl_Perfect_ApiProxyUrlReturned()
    {
        putenv('SUITE_API_HOST=api_proxy_host');
        $customerId = 30;
        $this->assertStringContainsString(
            'api_proxy_host',
            getenv('SUITE_API_HOST') . "/api/v2/internal/$customerId/contactlist"
        );
    }

    /**
     * @test
     */
    public function responseOfSuccessfulGetRequestShouldIndicateSuccess()
    {
        $this->expectSuccessfulGet(self::URL)->willReturn($this->apiSuccess());
        $response = $this->apiClient->get(self::URL);
        $this->assertTrue($response['success']);
    }

    /**
     * @test
     */
    public function assembleParametersAsGetQueryParameters()
    {
        $parameters = [
            'first' => 1,
            'second' => "sec",
            'third' => "th i rd",
        ];
        $this->expectSuccessfulGet(self::URL . '?first=1&second=sec&third=th+i+rd')->willReturn($this->apiSuccess());
        $response = $this->apiClient->get(self::URL, $parameters);
        $this->assertSuccessful($response);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulGetRequestShouldContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulGet(self::URL)->willReturn($this->apiSuccess());
        $response = $this->apiClient->get(self::URL);
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulRequestShouldContainAdditionalDataReceivedFromApi()
    {
        $this->expectSuccessfulGet(self::URL)->willReturn($this->apiSuccess(['data' => 'DATA']));
        $response = $this->apiClient->get(self::URL);
        $this->assertThat($response, $this->structure(['data' => 'DATA']));
    }

    /**
     * @test
     */
    public function responseOfSuccessfulPostRequestShouldIndicateSuccessAndContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulPost(self::URL, [])->willReturn($this->apiSuccess());
        $response = $this->apiClient->post(self::URL, []);
        $this->assertSuccessful($response);
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulPutRequestShouldIndicateSuccessAndContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulPut(self::URL, [])->willReturn($this->apiSuccess());
        $response = $this->apiClient->put(self::URL, []);
        $this->assertSuccessful($response);
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     */
    public function responseOfSuccessfulDeleteRequestShouldIndicateSuccessAndContainApiResponseCodeAndText()
    {
        $this->expectSuccessfulDelete(self::URL, [])->willReturn($this->apiSuccess());
        $response = $this->apiClient->delete(self::URL, []);
        $this->assertSuccessful($response);
        $this->assertResponseContains($response, self::API_SUCCESS_CODE, self::API_SUCCESS_TEXT);
    }

    /**
     * @test
     */
    public function responseOfBadApiRequestShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectGetYieldingBadResponse()->willReturn($this->apiFailure());
        $this->expectException(Error::class);
        $this->expectExceptionMessage('FAIL');
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     */
    public function responseOfUnsuccessfulApiRequestShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectGetYieldingRequestException();
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Could not execute API request.');
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     */
    public function responseContainingInvalidFormatShouldIndicateFailureAndContainErrorMessage()
    {
        $this->expectSuccessfulGet(self::URL)->willReturn($this->returnValue($this->createStream('NOT A JSON STRING')));
        $this->expectException(Error::class);
        $this->expectExceptionMessage('API response format was wrong');
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     */
    public function requestShouldContainAuthorizationHeaders()
    {
        $this->expectSuccessfulGet(self::URL, $this->expectedHeaders())->willReturn($this->apiSuccess());
        $this->apiClient->get(self::URL);
    }

    /**
     * @test
     */
    public function httpsIsUsedInRequestUrl()
    {
        $apiWrapper = new Client(
            new NullLogger(),
            $this->escherProvider,
            $this->guzzleClient,
            $this->requestFactory,
            new SuiteResponseProcessor(new NullLogger())
        );
        $this->expectSuccessfulGet(self::URL)->willReturn($this->apiSuccess());
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
        $this->assertThat(
            $response,
            $this->structure([
                'replyCode' => $code,
                'replyText' => $text,
            ])
        );
    }

    protected function apiHeaders(): Constraint
    {
        return $this->structure(['Content-Type' => 'application/json']);
    }

    protected function structure(array $array): Constraint
    {
        $result = $this->logicalAnd();
        $result->setConstraints(
            array_map(function ($key, $constraint) {
                return new class($key, $constraint) extends Constraint {
                    private $key;
                    private $constraint;

                    public function __construct($key, $constraint)
                    {
                        $this->key = $key;
                        $this->constraint = $constraint instanceof Constraint ? $constraint : new IsEqual($constraint);
                    }

                    public function matches($other): bool
                    {
                        return array_key_exists($this->key, $other) && $this->constraint->evaluate(
                                $other[$this->key],
                                '',
                                true
                            );
                    }

                    public function toString(): string
                    {
                        return "array has key '$this->key' and the corresponding value " . $this->constraint->toString(
                            );
                    }
                };
            }, array_keys($array), array_values($array))
        );
        return $result;
    }

    protected function expectSuccessfulPost($apiUrl, $expectedParams): InvocationMocker
    {
        $this->expectSuccessfulRequest('POST', $apiUrl, $this->apiHeaders(), json_encode($expectedParams));
        return $this->response->expects($this->any())->method('getBody');
    }

    protected function expectSuccessfulPut($apiUrl, $expectedParams): InvocationMocker
    {
        $this->expectSuccessfulRequest('PUT', $apiUrl, $this->apiHeaders(), json_encode($expectedParams));
        return $this->response->expects($this->any())->method('getBody');
    }


    protected function expectSuccessfulDelete($apiUrl, $expectedParams): InvocationMocker
    {
        $this->expectSuccessfulRequest('DELETE', $apiUrl, $this->apiHeaders(), json_encode($expectedParams));
        return $this->response->expects($this->any())->method('getBody');
    }

    private function expectSuccessfulGet($apiUrl, $expectedHeaders = null): InvocationMocker
    {
        $this->expectSuccessfulRequest('GET', $apiUrl, $expectedHeaders ?: $this->apiHeaders());
        return $this->response->expects($this->any())->method('getBody');
    }

    private function expectGetYieldingBadResponse(): InvocationMocker
    {
        $this->expectRequest('GET', self::URL, $this->apiHeaders())
            ->willThrowException(new BadResponseException('Bad response', $this->request, $this->response));

        return $this->response->expects($this->any())->method('getBody');
    }

    private function expectGetYieldingRequestException()
    {
        $this->expectRequest('GET', self::URL, $this->apiHeaders())
            ->willThrowException(new RequestException('Request exception', $this->request));
    }

    private function expectSuccessfulRequest($method, $uri = null, $headers = null, $body = null)
    {
        $this->expectRequest($method, $uri, $headers, $body)->willReturn($this->response);
    }

    private function expectRequest(string $method, $uri, $headers, $body = null): InvocationMocker
    {
        $this->expectEscherSigning($method);
        $this->request->expects($this->any())->method('getMethod')->willReturn($method);
        $this->request->expects($this->any())->method('getUri')->willReturn(new Uri($uri));
        $this->requestFactory->expects($this->once())->method('createRequest')
            ->with(
                $method,
                null === $uri ? $this->anything() : $uri,
                null === $headers ? $this->anything() : $headers,
                null === $body ? $this->anything() : $body
            )->willReturn($this->request);
        return $this->guzzleClient->expects($this->once())->method('send')->with($this->request);
    }

    protected function apiSuccess($data = [])
    {
        return $this->returnValue(
            $this->createStream(
                json_encode(
                    [
                        'replyCode' => self::API_SUCCESS_CODE,
                        'replyText' => self::API_SUCCESS_TEXT,
                    ] + $data
                )
            )
        );
    }

    private function apiFailure(): ReturnStub
    {
        return $this->returnValue(
            $this->createStream(
                json_encode([
                    'replyCode' => self::API_FAILURE_CODE,
                    'replyText' => self::API_FAILURE_TEXT,
                ])
            )
        );
    }

    private function expectedHeaders(): Constraint
    {
        return $this->structure($this->allHeaders());
    }

    private function contentTypeHeader(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    private function allHeaders(): array
    {
        return $this->contentTypeHeader() + $this->authHeaders();
    }

    private function authHeaders(): array
    {
        return ['X-Dummy-Auth' => 'SIGNATURE'];
    }

    private function expectEscherSigning($method)
    {
        $this->escherProvider->expects($this->once())->method('createEscher')->willReturn(
            $this->returnValue($this->escher)
        );
        $this->escherProvider->expects($this->any())->method('getEscherKey')->willReturn(
            $this->returnValue(self::ESCHER_KEY)
        );
        $this->escherProvider->expects($this->any())->method('getEscherSecret')->willReturn(
            $this->returnValue(self::ESCHER_SECRET)
        );

        $this->escher->expects($this->once())->method('signRequest')
            ->with(
                self::ESCHER_KEY,
                self::ESCHER_SECRET,
                $this->equalTo($method),
                $this->stringContains('://'),
                $this->isType('string'),
                $this->contentTypeHeader()
            )
            ->willReturn($this->returnValue($this->allHeaders()));
    }

    private function createStream(string $contents): BufferStream
    {
        $stream = new BufferStream();
        $stream->write($contents);
        return $stream;
    }

}
