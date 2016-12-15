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
        $app->get('/{customerId}/email/{campaignId}', function(int $customerId, int $campaignId) {
            return self::success(['id' => $campaignId, 'customer_id' => $customerId]);
        });

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

    private static function success($data = '')
    {
        return json_encode(['replyCode' => 0, 'replyText' => 'OK', 'data' => $data]);
    }

    private static function error($message, $data = '')
    {
        return json_encode(['replyCode' => 1, 'replyText' => $message, 'data' => $data]);
    }
}
