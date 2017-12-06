<?php

namespace Suite\Api\Acceptance;

use PHPUnit_Framework_Constraint;

use Escher\Provider as EscherProvider;
use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\SuiteResponseProcessor;
use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class ClientTest extends AcceptanceBaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->cleanupLogFile();
    }

    public function tearDown()
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

        $this->assertThat($client->get("{$this->apiBaseUrl}/"), $this->isSuccessfulApiResponse());
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

    private function isSuccessfulApiResponse() : PHPUnit_Framework_Constraint
    {
        return $this->structure([
            'replyCode' => 0,
            'replyText' => 'OK',
            'data' => '',
        ]);
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
    private function badEscherProvider():EscherProvider
    {
        return new EscherProvider('foo/bar/invalid_credential', 'key', 'secret', []);
    }

    private function cleanupLogFile(): void
    {
        if (file_exists('retry.log')) {
            unlink('retry.log');
        }
    }
}
