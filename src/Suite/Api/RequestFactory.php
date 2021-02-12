<?php

namespace Suite\Api;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class RequestFactory
{
    public function createRequest($method, $uri, array $headers, $body) : RequestInterface
    {
        return new Request($method, $uri, $headers, $body);
    }
}
