<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class ExternalEventTest extends AcceptanceBaseTestCase
{
    /**
     * @test
     */
    public function getListEndPoint()
    {
        $list = $this->factory->createExternalEvent()->getList(123456);
        $this->assertCount(2, $list);

        $this->assertEquals(1, $list[0]['id']);
        $this->assertEquals([3], $list[0]['usages']['email_ids']);
        $this->assertEquals(2, $list[1]['id']);
        $this->assertEquals([4], $list[1]['usages']['program_ids']);
    }
}
