<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;
use PHPUnit\Framework\Attributes\Test;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AdministratorTest extends AcceptanceBaseTestCase
{
    #[Test]
    public function emailCampaignEndPoint()
    {
        $list = $this->factory->createAdministrator()->getList(1);

        $this->assertCount(2, $list);
        $this->assertEquals(1, $list[0]['id']);
        $this->assertEquals('admin', $list[0]['username']);
        $this->assertEquals(2, $list[1]['id']);
        $this->assertEquals('admin2', $list[1]['username']);
    }
}
