<?php

namespace Suite\Api\Acceptance;

use Emartech\TestHelper\BaseTestCase;
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
        $escherProvider = new EscherProvider('foo/bar/baz', 'key', 'secret', []);
        $client = Client::create($this->dummyLogger, $escherProvider, new SuiteResponseProcessor($this->dummyLogger));

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
