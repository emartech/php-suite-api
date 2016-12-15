<?php

namespace Suite\Api\Acceptance;

use PHPUnit_Framework_Constraint;

use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\EscherProvider;
use Suite\Api\SuiteResponseProcessor;
use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class ClientTest extends AcceptanceBaseTestCase
{
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
     * @return EscherProvider
     */
    private function badEscherProvider():EscherProvider
    {
        return new EscherProvider('foo/bar/invalid_credential', 'key', 'secret', []);
    }
}
