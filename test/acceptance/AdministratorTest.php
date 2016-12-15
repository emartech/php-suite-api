<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class AdministratorTest extends AcceptanceBaseTestCase
{
    /**
     * @test
     */
    public function emailCampaignEndPoint()
    {
        $list = $this->factory->createAdministrator()->getList(1);
        $this->assertCount(2, $list);
        $this->assertThat($list[0], $this->structure(['id' => 1, 'username' => 'admin']));
        $this->assertThat($list[1], $this->structure(['id' => 2, 'username' => 'admin2']));
    }
}
