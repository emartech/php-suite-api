<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;


class LaunchTest extends AcceptanceBaseTestCase
{
    /**
     * @test
     */
    public function emailLaunchEndPoint()
    {
        $this->assertNull($this->factory->createLaunch()->launch(1, 1));
    }
}
