<?php

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppCache extends HttpCache
{
    const ENV_HTTP_CACHE_REDIS_HOST     = 'HTTP_CACHE_REDIS_HOST';
    const ENV_HTTP_CACHE_REDIS_PORT     = 'HTTP_CACHE_REDIS_PORT';
    const ENV_HTTP_CACHE_REDIS_DATABASE = 'HTTP_CACHE_REDIS_DATABASE';

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            'default_ttl'            => 0,
        ];
    }

//    protected function invalidate(Request $request, $catch = false)
//    {
//        if ('PURGE' !== $request->getMethod()) {
//            return parent::invalidate($request, $catch);
//        }
//
//        if ('127.0.0.1' !== $request->getClientIp()) {
//            return new Response(
//                'Invalid HTTP method',
//                Response::HTTP_BAD_REQUEST
//            );
//        }
//
//        $response = new Response();
//        if ($this->getStore()->purge($request->getUri())) {
//            $response->setStatusCode(Response::HTTP_OK, 'Purged');
//        } else {
//            $response->setStatusCode(Response::HTTP_NOT_FOUND, 'Not found');
//        }
//
//        return $response;
//    }

//    /**
//     * @return RedisStore
//     */
//    protected function createStore()
//    {
//        return $this->createRedisStore();
//    }
//
//    /**
//     * @return mixed
//     */
//    private function getParameters()
//    {
//        $parser = new \Symfony\Component\Yaml\Parser();
//
//        return $parser->parse(file_get_contents(__DIR__ . '/config/parameters.yml'));
//    }
//
//    /**
//     * @return MemcachedStore
//     */
//    private function createMemcachedStore()
//    {
//        $parameters = $this->getParameters();
//
//        return new MemcachedStore(
//            [
//                'persistentId' => 'api_cache',
//            ],
//            [
//                [
//                    'host' => $parameters['parameters']['memcached_host'],
//                    'port' => $parameters['parameters']['memcached_port'],
//                ],
//            ]
//        );
//    }
//
//    /**
//     * @return RedisStore
//     */
//    private function createRedisStore()
//    {
//        $parameters = [
//            'enabled'      => true,
//            'debug'        => false,
//            'persistentId' => 'api_cache',
//        ];
//
//        $redisHost = getenv(self::ENV_HTTP_CACHE_REDIS_HOST);
//        $redisPort = getenv(self::ENV_HTTP_CACHE_REDIS_PORT);
//        $redisDb = getenv(self::ENV_HTTP_CACHE_REDIS_DATABASE);
//
//        if (false !== $redisHost) {
//            $parameters['host'] = $redisHost;
//        }
//
//        if (false !== $redisPort) {
//            $parameters['port'] = $redisPort;
//        }
//
//        if (false !== $redisDb) {
//            $parameters['database'] = $redisDb;
//        }
//
//        return new RedisStore($parameters);
//    }
//
//    /**
//     * @param Request $request
//     * @param bool    $catch
//     *
//     * @return Response
//     */
//    protected function invalidate(Request $request, $catch = false)
//    {
//        if ('PURGE' == $request->getMethod()) {
//            $purge = [
//                'method' => 'purge',
//                'uri'    => $request->getUri(),
//            ];
//
//            $response = new Response();
//            $response->setStatusCode(200, $this->getStore()->purge($purge) ? 'Purged' : 'Not found');
//
//            return $response;
//        }
//
//        if ('BAN' == $request->getMethod()) {
//            $ban = [
//                'method' => 'ban',
//                'tags'   => $request->headers->get('X-Cache-Tags', [], false),
//            ];
//
//            $response = new Response();
//            $response->setStatusCode(200, $this->getStore()->purge($ban) ? 'Banned' : 'Not found');
//
//            return $response;
//        }
//
//        return parent::invalidate($request, $catch);
//    }
}
