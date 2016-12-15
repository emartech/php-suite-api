<?php

namespace Suite\Api\Acceptance;

use Emartech\TestHelper\BaseTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit_Framework_Constraint;

use Psr\Log\LoggerInterface;
use Suite\Api\Client;
use Suite\Api\Error;
use Suite\Api\EscherProvider;
use Suite\Api\SuiteResponseProcessor;

class AcceptanceTest extends BaseTestCase
{
    /**
     * @var LoggerInterface
     */
    private $spyLogger;

    protected function setUp()
    {
        parent::setUp();
        $this->spyLogger = new Logger('spy', [new StreamHandler(fopen('/tmp/php-suite-api.log', 'a'))]);
    }

    /**
     * @test
     */
    public function sendingRequestWorks()
    {
        $client = $this->createClient(new EscherProvider('foo/bar/baz', 'key', 'secret', []));

        $this->assertThat($client->get('http://localhost:7984/'), $this->isSuccessfulApiResponse());
    }
    /**
     * @test
     */
    public function authenticationWorks()
    {
        $client = $this->createClient(new EscherProvider('foo/bar/invalid_credential', 'key', 'secret', []));

        try {
            $client->get('http://localhost:7984/');
            $this->fail('An exception was expected');
        } catch (Error $exception) {
            $this->assertEquals('Authentication error.', $exception->getMessage());
        }
    }

    /**
     * @return PHPUnit_Framework_Constraint
     */
    private function isSuccessfulApiResponse()
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
    private function createClient($escherProvider):Client
    {
        return Client::create($this->spyLogger, $escherProvider, new SuiteResponseProcessor($this->spyLogger));
    }
}
