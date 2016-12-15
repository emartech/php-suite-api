<?php

namespace Suite\Api;

use PHPUnit_Framework_MockObject_MockObject;
use Emartech\TestHelper\BaseTestCase;
use Suite\Api\Administrator\Administrator;
use Suite\Api\Administrator\EndPoints;

class AdministratorTest extends BaseTestCase
{

    const API_BASE_URL = 'api_base_url';
    const API_SUCCESS_TEXT = 'OK';
    const API_SUCCESS_CODE = 0;

    /** @var EndPoints */
    private $endPoints;

    /** @var Client|PHPUnit_Framework_MockObject_MockObject */
    private $apiClient;

    /** @var Administrator */
    private $administrator;

    /* @var int */
    private $customerId;


    protected function setUp()
    {
        parent::setUp();

        $this->endPoints = new EndPoints(self::API_BASE_URL);
        $this->apiClient = $this->mock(Client::class);
        $this->administrator = new Administrator($this->apiClient, $this->endPoints);
        $this->customerId = 555;
    }


    /**
     * @test
     */
    public function getList_Perfect_Perfect()
    {
        $admins = array(
            $this->createAdmin("1", "admin1"),
            $this->createAdmin("2", "admin2")
        );

        $this->apiClient->expects($this->once())->method('get')
            ->with($this->endPoints->administratorList($this->customerId))
            ->will($this->apiSuccess($admins));

        $responseData = $this->administrator->getList($this->customerId);
        $this->assertEquals($admins, $responseData);
    }


    /**
     * @test
     */
    public function get_ApiFailure_ExceptionThrown()
    {
        $this->expectApiFailure();

        try {
            $this->administrator->getList($this->customerId);
        } catch (RequestFailed $e) {
            return;
        }

        $this->fail('No exception was thrown.');
    }


    private function expectApiFailure()
    {
        $this->apiClient->expects($this->once())->method('get')
            ->will($this->throwException(new \Exception()));
    }


    protected function apiSuccess($data)
    {
        return $this->returnValue([
            'success' => true,
            'replyCode' => self::API_SUCCESS_CODE,
            'replyText' => self::API_SUCCESS_TEXT,
            'data' => $data
        ]);
    }


    private function createAdmin(string $id, string $username): array
    {
        return array(
            "id" => "{$id}",
            "username" => "{$username}",
            "email" => "",
            "first_name" => "",
            "last_name" => "",
            "interface_language" => "en",
            "default_upages_lang" => "en",
            "access_level" => "0",
            "position" => "",
            "title" => "0",
            "tz" => "",
            "mobile_phone" => "",
            "may_delete" => "n",
            "last_invitation_action_date" => "",
            "pwd_update_interval" => "90",
            "two_fa_auth_enabled" => 0,
            "mobile_phone_verified" => 0,
            "distance_unit" => "km"
        );
    }
}
