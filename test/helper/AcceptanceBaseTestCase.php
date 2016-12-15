<?php

namespace Suite\Api\Test\Helper;

use Emartech\TestHelper\BaseTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Suite\Api\EscherProvider;
use Suite\Api\Factory;

class AcceptanceBaseTestCase extends BaseTestCase
{
    /**
     * @var LoggerInterface
     */
    protected $spyLogger;

    /**
     * @var EscherProvider
     */
    protected $escherProvider;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $apiBaseUrl = 'http://localhost:7984';

    protected function setUp()
    {
        parent::setUp();
        $this->spyLogger = new Logger('spy', [new StreamHandler(fopen('/tmp/php-suite-api.log', 'a'))]);
        $this->escherProvider = new EscherProvider('foo/bar/baz', 'key', 'secret', []);
        $this->factory = Factory::create($this->spyLogger, $this->escherProvider, $this->apiBaseUrl);
    }
}
