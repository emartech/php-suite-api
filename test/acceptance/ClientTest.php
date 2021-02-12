<?php

namespace Suite\Api\Acceptance;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit_Framework_Constraint;

use Escher\Provider as EscherProvider;
use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\SuiteResponseProcessor;
use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class ClientTest extends AcceptanceBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->cleanupLogFile();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupLogFile();
    }

    /**
     * @test
     */
    public function sendingRequestWorks()
    {
        $client = $this->createClient($this->escherProvider);

        $successfulApiResponse = [
            'replyCode' => 0,
            'replyText' => 'OK',
            'data' => '',
            'success' => true,
        ];
        $this->assertEquals($successfulApiResponse, $client->get("{$this->apiBaseUrl}/"));
    }
    /**
     * @test
     */
    public function authenticationWorks()
    {
        $client = $this->createClient($this->badEscherProvider());

        try {
            $client->get("{$this->apiBaseUrl}/");
            $this->fail('An exception was expected');
        } catch (Error $exception) {
            $this->assertEquals('Authentication error.', $exception->getMessage());
        }
    }

    /**
     * @test
     */
    public function retryWorks()
    {
        $client = $this->createRetryClient();

        try {
            $client->get("{$this->apiBaseUrl}/serverError");
        } catch (\Exception $e) {
            $this->assertEquals(2, $client->get("{$this->apiBaseUrl}/retryCount")['data']);
        }
    }

    /**
     * @param $escherProvider
     * @return Client
     */
    private function createClient($escherProvider) : Client
    {
        return Client::create($this->spyLogger, $escherProvider, new SuiteResponseProcessor($this->spyLogger));
    }

    /**
     * @return Client
     */
    private function createRetryClient() : Client
    {
        return Client::createWithRetry($this->spyLogger, $this->escherProvider, new SuiteResponseProcessor($this->spyLogger), 1);
    }

    /**
     * @return EscherProvider
     */
    private function badEscherProvider() : EscherProvider
    {
        return new EscherProvider('foo/bar/invalid_credential', 'key', 'secret', []);
    }

    private function cleanupLogFile()
    {
        if (file_exists('retry.log')) {
            unlink('retry.log');
        }
    }
}
