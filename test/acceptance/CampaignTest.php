<?php

namespace Suite\Api\Acceptance;

use Suite\Api\Test\Helper\AcceptanceBaseTestCase;
use PHPUnit\Framework\Attributes\Test;


#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CampaignTest extends AcceptanceBaseTestCase
{
    #[Test]
    public function emailCampaignEndPoint()
    {
        $this->assertEquals(1, $this->factory->createCampaign()->getById(1, 1)['id']);
    }

    #[Test]
    public function emailCampaignListEndPoint()
    {
        $list = $this->factory->createCampaign()->getList(1);
        $this->assertCount(2, $list);

        $this->assertEquals(2, $list[0]['id']);
        $this->assertEquals(3, $list[1]['id']);
    }

    #[Test]
    public function previewHtml()
    {
        $this->assertEquals('html version', $this->factory->createPreview()->getHtml(1, 1));
    }

    #[Test]
    public function previewText()
    {
        $this->assertEquals('text version', $this->factory->createPreview()->getText(1, 1));
    }

    #[Test]
    public function previewMobile()
    {
        $this->assertEquals('mobile version', $this->factory->createPreview()->getMobile(1, 1));
    }

    #[Test]
    public function emailCampaignDeleteEndPoint()
    {
        $response = $this->factory->createCampaign()->deleteById(1, 1);
        $this->assertNull($response);
    }
}
