<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class CampaignTest extends AcceptanceBaseTestCase
{
    /**
     * @test
     */
    public function emailCampaignEndPoint()
    {
        $this->assertThat($this->factory->createCampaign()->getById(1, 1), $this->structure(['id' => 1]));
    }

    /**
     * @test
     */
    public function emailCampaignListEndPoint()
    {
        $list = $this->factory->createCampaign()->getList(1);
        $this->assertCount(2, $list);
        $this->assertThat($list[0], $this->structure(['id' => 2]));
        $this->assertThat($list[1], $this->structure(['id' => 3]));
    }

    /**
     * @test
     */
    public function previewHtml()
    {
        $this->assertEquals('html version', $this->factory->createPreview()->getHtml(1, 1));
    }

    /**
     * @test
     */
    public function previewText()
    {
        $this->assertEquals('text version', $this->factory->createPreview()->getText(1, 1));
    }

    /**
     * @test
     */
    public function previewMobile()
    {
        $this->assertEquals('mobile version', $this->factory->createPreview()->getMobile(1, 1));
    }

    /**
     * @test
     */
    public function emailCampaignDeleteEndPoint()
    {
        $response = $this->factory->createCampaign()->deleteById(1, 1);
        $this->assertSame($response, null);
    }
}
