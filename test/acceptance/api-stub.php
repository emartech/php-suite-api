<?php

require_once __DIR__.'/../../vendor/autoload.php';

try {
    $escherProvider = new \Suite\Api\EscherProvider('foo/bar/baz', 'irrelevant', 'irrelevant', ['key' => 'secret']);
    $escherProvider->createEscher()->authenticate($escherProvider->getKeyDB());
    echo json_encode(['replyCode' => 0, 'replyText' => 'Success.']);
} catch (EscherException $exception) {
    header('HTTP/1.1 403 Unauthorized');
    echo json_encode(['replyCode' => 1, 'replyText' => 'Authentication error.']);
} catch (\Exception $exception) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['replyCode' => 2, 'replyText' => (string)$exception]);
}
