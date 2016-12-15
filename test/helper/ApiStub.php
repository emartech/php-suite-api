<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiStub
{
    public static function setUp()
    {
        $app = new \Silex\Application();
        $app->before(function () {
            try {
                $escherProvider = new \Suite\Api\EscherProvider('foo/bar/baz', 'irrelevant', 'irrelevant', ['key' => 'secret']);
                $escherProvider->createEscher()->authenticate($escherProvider->getKeyDB());
                return null;
            } catch (EscherException $exception) {
                return new Response(json_encode(['replyCode' => 1, 'replyText' => 'Authentication error.', 'data' => '']), 403);
            } catch (\Exception $exception) {
                return new Response(json_encode(['replyCode' => 1, 'replyText' => (string)$exception, 'data' => '']), 500);
            }
        });

        $app->get('/', function(Request $request) {
            return json_encode(['replyCode' => 0, 'replyText' => 'OK', 'data' => '']);
        });
        return $app;
    }
}
