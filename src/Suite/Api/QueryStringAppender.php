<?php

namespace Suite\Api;

class QueryStringAppender
{
    public static function appendParamsToUrl(string $url, array $data): string
    {
        return $url . (empty($data) ? '' : '?' . http_build_query($data));
    }
}