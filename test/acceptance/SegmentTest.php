<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class SegmentTest extends AcceptanceBaseTestCase
{
    /**
     * @test
     */
    public function getListEndPoint()
    {
        $list = $this->factory->createSegment()->getList(123456);
        $this->assertCount(2, $list);

        $this->assertEquals(1, $list[0]['id']);
        $this->assertEquals(3, $list[0]['predefinedSegmentId']);
        $this->assertEquals(2, $list[1]['id']);
        $this->assertEquals(5, $list[1]['predefinedSegmentId']);
    }
}
