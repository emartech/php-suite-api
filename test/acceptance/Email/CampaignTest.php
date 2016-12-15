<?php

namespace Suite\Api\Acceptance\Email;

use Suite\Api\Factory;
use Suite\Api\Test\Helper\AcceptanceBaseTestCase;

class CampaignTest extends AcceptanceBaseTestCase
{
    /**
     * @test
     */
    public function emailCampaignEndPoint()
    {
        $campaignId = 654312;
        $customerId = 123456;
        $response = $this->factory->createCampaign()->getById($customerId, $campaignId);
        $this->assertThat($response, $this->structure(['id' => $campaignId, 'customer_id' => $customerId]));
    }
}