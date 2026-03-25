<?php

error_reporting(E_ALL & ~E_DEPRECATED);

use Suite\Api\Test\Helper\ApiStub;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helper/ApiStub.php';

ApiStub::setUp()->run();
