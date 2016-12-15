<?php

namespace Suite\Api\Test\Helper;

use EscherException;
use Exception;

use Silex\Application;
use Suite\Api\EscherProvider;
use Symfony\Component\HttpFoundation\Response;

class ApiStub
{
    public static function setUp()
    {
        $app = new Application();

        $app->before(self::authenticateWithEscher());
        $app->error(self::handleError());

        $app->get('/', self::clientTestEndPoint());

        foreach (include __DIR__.'/stubs.php' as $route => $data) {
            $app->get($route, function () use ($data) { return self::success($data); });
        }

        return $app;
    }

    private static function authenticateWithEscher()
    {
        return function () {
            try {
                $escherProvider = new EscherProvider('foo/bar/baz', 'irrelevant', 'irrelevant', ['key' => 'secret']);
                $escherProvider->createEscher()->authenticate($escherProvider->getKeyDB());
                return null;
            } catch (EscherException $exception) {
                return new Response(self::error('Authentication error.'), 403);
            } catch (Exception $exception) {
                return new Response(self::error($exception->getMessage(), (string)$exception), 500);
            }
        };
    }

    private static function clientTestEndPoint()
    {
        return function () {
            return self::success();
        };
    }

    private static function handleError()
    {
        return function (\Exception $e) {
            return new Response(json_encode(['replyCode' => 1, 'replyText' => $e->getMessage(), 'data' => (string)$e]));
        };
    }

    private static function success($data = '""')
    {
        return '{"replyCode":0, "replyText" : "OK", "data" : '.$data.'}';
    }

    private static function error($message, $data = '""')
    {
        return '{"replyCode":1, "replyText" : "'.$message.'", "data" : '.$data.'}';
    }
}
