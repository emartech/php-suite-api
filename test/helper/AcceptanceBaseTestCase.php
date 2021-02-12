<?php

namespace Suite\Api\Test\Helper;

use Escher\Provider as EscherProvider;
use Psr\Log\LoggerInterface;
use Suite\Api\Factory;

class AcceptanceBaseTestCase extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->spyLogger = $this->createMock(LoggerInterface::class);
        $this->escherProvider = new EscherProvider('foo/bar/baz', 'key', 'secret', []);
        $this->factory = Factory::create($this->spyLogger, $this->escherProvider, $this->apiBaseUrl);
    }

}
