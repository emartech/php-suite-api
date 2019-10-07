<?php

namespace Suite\Api\Test\Helper;

use Escher\Provider as EscherProvider;
use Emartech\TestHelper\BaseTestCase;
use Psr\Log\LoggerInterface;
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
        $this->spyLogger = $this->getMock(LoggerInterface::class);
        $this->escherProvider = new EscherProvider('foo/bar/baz', 'key', 'secret', []);
        $this->factory = Factory::create($this->spyLogger, $this->escherProvider, $this->apiBaseUrl);
    }
}
