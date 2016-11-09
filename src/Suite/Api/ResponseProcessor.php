<?php

namespace Suite\Api;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

interface ResponseProcessor
{
    const API_RESPONSE_FORMAT_WAS_WRONG = 'API response format was wrong';
    const UNKNOWN_ERROR = 'Unknown error';

    public function processResponse(RequestInterface $request, Response $response);
}
