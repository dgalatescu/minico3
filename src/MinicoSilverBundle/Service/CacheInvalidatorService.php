<?php

namespace MinicoSilverBundle\Service;

use Doctrine\Common\Cache\RedisCache;
use Doctrine\ORM\NoResultException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class CacheInvalidatorService
{
    const ID = 'est.cache_invalidator';

    /**
     * @var Router
     */
    private $router;

//    /**
//     * @var RedisCache
//     */
//    private $cache;

    /**
     * @param string $route
     * @param array  $params
     *
     * @return bool
     * @throws \Exception
     */
    public function invalidateRoute($route, $params = [])
    {
        $url = $this->router->generate($route, $params, Router::ABSOLUTE_URL);

        $client = new Client();

        try {
            $client->request('PURGE', $url);
        } catch (RequestException $e) {
            throw new \Exception('Fail to invalidate route.'.$e->getMessage());
        }

        return true;
    }

    /**
     * @param Router $router
     *
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

//    /**
//     * @param RedisCache $cache
//     *
//     * @return $this
//     */
//    public function setCache($cache)
//    {
//        $this->cache = $cache;
//
//        return $this;
//    }

    /**
     * @return Client
     */
    protected function getGuzzleHttpClient()
    {
        return new Client();
    }
}
