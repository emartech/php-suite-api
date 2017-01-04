<?php

namespace Suite\Api;

use Escher\Provider as EscherProvider;
use Psr\Log\LoggerInterface;
use Suite\Api\Administrator\Administrator;
use Suite\Api\Administrator\EndPoints as AdminEndPoints;
use Suite\Api\Email\Campaign;
use Suite\Api\Email\EndPoints as CampaignEndPoints;
use Suite\Api\Email\Launch;
use Suite\Api\Email\Preview;
use Suite\Api\ExternalEvent\EndPoints as ExternalEventEndPoints;
use Suite\Api\ExternalEvent\ExternalEvent;

class Factory
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * @var string
     */
    private $apiBaseUrl;

    public static function create(LoggerInterface $logger, EscherProvider $escherProvider, $apiBaseUrl)
    {
        $responseProcessor = new SuiteResponseProcessor($logger);
        $apiClient = Client::create($logger, $escherProvider, $responseProcessor);
        return new self($apiClient, $apiBaseUrl);
    }

    public function __construct(Client $apiClient, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function createCampaign()
    {
        return new Campaign($this->apiClient, new CampaignEndPoints($this->apiBaseUrl));
    }

    public function createPreview()
    {
        return new Preview($this->apiClient, new CampaignEndPoints($this->apiBaseUrl));
    }

    public function createAdministrator()
    {
        return new Administrator($this->apiClient, new AdminEndPoints($this->apiBaseUrl));
    }

    public function createExternalEvent()
    {
        return new ExternalEvent($this->apiClient, new ExternalEventEndPoints($this->apiBaseUrl));
    }

    public function createLaunch()
    {
        return new Launch($this->apiClient, new CampaignEndPoints($this->apiBaseUrl));
    }

}
