<?php

namespace Suite\Api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseProcessor
{
    const API_RESPONSE_FORMAT_WAS_WRONG = 'API response format was wrong';
    const UNKNOWN_ERROR = 'Unknown error';

    public function processResponse(RequestInterface $request, ResponseInterface $response);
}
