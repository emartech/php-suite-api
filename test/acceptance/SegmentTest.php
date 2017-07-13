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
        $this->assertThat($list[0], $this->structure(['id' => 1, 'predefinedSegmentId' => 3]));
        $this->assertThat($list[1], $this->structure(['id' => 2, 'predefinedSegmentId' => 5]));
    }
}
