<?php

namespace Suite\Api\Test\Helper;

use Escher\Exception as EscherException;
use Escher\Provider as EscherProvider;
use Exception;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiStub
{
    public const LIST_ID_FOR_EMPTY_LIST = 123;
    public const LIST_ID_FOR_LIST_WITH_SINGLE_CHUNK = 456;
    public const LIST_ID_FOR_LIST_WITH_MULTIPLE_CHUNKS = 789;
    public const LIST_ID_FOR_WRONG_RESPONSE = 666;

    public static function setUp()
    {
        $app = new Application();

        $app->before(self::authenticateWithEscher());
        $app->error(self::handleError());

        $app->get('/', self::clientTestEndPoint());

        foreach (include __DIR__.'/stubs.php' as $route => $data) {
            $app->get($route, function () use ($data) { return self::success($data); });
        }

        $app->post('/{customerId}/email/{campaignId}/preview/', function (Request $request) {
            $params = json_decode($request->getContent(), true);
            return new Response(self::success('"'.$params['version'].' version"'));
        });

        $app->post('/{customerId}/email/{campaignId}/launch/', function (Request $request) {
            return new Response(self::success("null"));
        });

        $app->post('/{customerId}/email/delete/', function (Request $request) {
            return new Response(self::success("null"));
        });

        $app->get('/serverError', function (Request $request) {
            self::logRetry();
            return new Response(self::error("null"), 500);
        });

        $app->get('/retryCount', function (Request $request) {
            return new Response(self::success(self::getRetryCount()));
        });

        $app->get('/{customerId}/contactlist/{contactListId}/contactIds', function (Request $request, $contactListId) {
            return match ($contactListId) {
                (string) self::LIST_ID_FOR_EMPTY_LIST => new Response(self::success('{"value":[],"next":null}')),
                (string) self::LIST_ID_FOR_LIST_WITH_SINGLE_CHUNK => new Response(self::success('{"value":[1,2,3],"next":null}')),
                (string) self::LIST_ID_FOR_LIST_WITH_MULTIPLE_CHUNKS => match ($request->query->get('$skiptoken') ?? '0') {
                    '0' => new Response(self::success('{"value":[1,2,3],"next":"/contactlist/'.$contactListId.'/contactIds?$skiptoken=1"}')),
                    '1' => new Response(self::success('{"value":[4,5,6],"next":"/contactlist/'.$contactListId.'/contactIds?$skiptoken=2"}')),
                    '2' => new Response(self::success('{"value":[7,8,9],"next":"/contactlist/'.$contactListId.'/contactIds?$skiptoken=3"}')),
                    '3' => new Response(self::success('{"value":[10,11],"next":null}')),
                },
                (string) self::LIST_ID_FOR_WRONG_RESPONSE => new Response(self::error('invalid response format')),
                default => new Response(self::error('contact list not found'), 404),
            };
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

    private static function success($data = '""')
    {
        return '{"replyCode":0, "replyText" : "OK", "data" : '.$data.'}';
    }

    private static function error($message, $data = '""')
    {
        return '{"replyCode":1, "replyText" : "'.$message.'", "data" : '.$data.'}';
    }

    private static function logRetry()
    {
        file_put_contents('retry.log', "called\n", FILE_APPEND);
    }

    private static function getRetryCount()
    {
        $content = file_get_contents('retry.log');
        return count(explode("\n", trim($content)));
    }
}
