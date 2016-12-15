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
        $this->assertThat($list[0], $this->structure(['id' => 1, 'usages' => $this->structure(['email_ids' => [3]])]));
        $this->assertThat($list[1], $this->structure(['id' => 2, 'usages' => $this->structure(['program_ids' => [4]])]));
    }
}
