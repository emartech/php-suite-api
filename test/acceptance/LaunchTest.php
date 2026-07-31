<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;
use PHPUnit\Framework\Attributes\Test;


#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LaunchTest extends AcceptanceBaseTestCase
{
    #[Test]
    public function emailLaunchEndPoint()
    {
        $this->assertNull($this->factory->createLaunch()->launch(1, 1));
    }
}
