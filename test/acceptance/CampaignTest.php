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
        $this->assertEquals(1, $this->factory->createCampaign()->getById(1, 1)['id']);
    }

    /**
     * @test
     */
    public function emailCampaignListEndPoint()
    {
        $list = $this->factory->createCampaign()->getList(1);
        $this->assertCount(2, $list);

        $this->assertEquals(2, $list[0]['id']);
        $this->assertEquals(3, $list[1]['id']);
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
        $this->assertNull($response);
    }
}
