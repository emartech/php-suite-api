<?php

namespace Suite\Api\Acceptance;

use Emartech\TestHelper\BaseTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit_Framework_Constraint;

use Suite\Api\Client;
use Suite\Api\EscherProvider;
use Suite\Api\SuiteResponseProcessor;

class AcceptanceTest extends BaseTestCase
{
    /**
     * @test
     */
    public function sendingRequestWorks()
    {
        $spyLogger = new Logger('spy', [new StreamHandler(fopen('/tmp/php-suite-api.log', 'a'))]);
        $escherProvider = new EscherProvider('foo/bar/baz', 'key', 'secret', []);
        $client = Client::create($spyLogger, $escherProvider, new SuiteResponseProcessor($spyLogger));

        $this->assertThat($client->get('http://localhost:7984/'), $this->isSuccessFulApiResponse());
    }

    /**
     * @return PHPUnit_Framework_Constraint
     */
    private function isSuccessFulApiResponse()
    {
        return $this->structure([
            'replyCode' => 0,
            'replyText' => 'Success.',
            'success' => true,
        ]);
    }
}
