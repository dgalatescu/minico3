<?php

use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MemcachedStore implements StoreInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var \Memcached
     */
    private $memcached;

    /**
     * MemcachedStore constructor.
     * @param array $options
     * @param array $servers
     */
    public function __construct(array $options, array $servers)
    {
        $this->options = $this->getDefaultOptions($options);

        $this->memcached = new \Memcached($this->options['persistentId']);
        $this->memcached->addServers($servers);
    }

    /**
     * Locates a cached Response for the Request provided.
     *
     * @param Request $request A Request instance
     *
     * @return Response|null A Response instance, or null if no cache entry was found
     */
    public function lookup(Request $request)
    {
        $key = $this->getCacheKey($request);

        if (!$entries = $this->getMetadata($key)) {
            return null;
        }

        $match = null;

        foreach ($entries as $entry) {
            if ($this->requestsMatch(isset($entry[1]['vary'][0]) ? $entry[1]['vary'][0] : '', $request->headers->all(), $entry[0])) {
                $match = $entry;
                break;
            }
        }

        if (null === $match) {
            return null;
        }

        list($req, $headers) = $match;

        $body = $this->memcached->get($headers['x-content-digest'][0]);

        if (!empty($body)) {
            return $this->restoreResponse($headers, $body);
        }

        return null;
    }

    /**
     * Writes a cache entry to the store for the given Request and Response.
     *
     * Existing entries are read and any that match the response are removed. This
     * method calls write with the new list of cache entries.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     *
     * @return string The key under which the response is stored
     */
    public function write(Request $request, Response $response)
    {
        $key = $this->getCacheKey($request);
        $storedEnv = $this->persistRequest($request);

        // write the response body to the entity store if this is the original response
        if (!$response->headers->has('X-Content-Digest')) {
            $digest = $this->generateContentDigest($response);

            if (false === $this->memcached->set($digest, $response->getContent())) {
                throw new \RuntimeException('Unable to store the entity.');
            }

            $response->headers->set('X-Content-Digest', $digest);

            if (!$response->headers->has('Transfer-Encoding')) {
                $response->headers->set('Content-Length', strlen($response->getContent()));
            }
        }

        // read existing cache entries, remove non-varying, and add this one to the list
        $entries = array();
        $vary = $response->headers->get('vary');

        foreach ($this->getMetadata($key) as $entry) {
            if (!isset($entry[1]['vary'][0])) {
                $entry[1]['vary'] = array('');
            }

            if ($vary != $entry[1]['vary'][0] || !$this->requestsMatch($vary, $entry[0], $storedEnv)) {
                $entries[] = $entry;
            }
        }

        $headers = $this->persistResponse($response);
        unset($headers['age']);

        array_unshift($entries, array($storedEnv, $headers));
        if (false === $this->memcached->set($key, serialize($entries))) {
            throw new \RuntimeException('Unable to store the metadata.');
        }

        return $key;
    }

    /**
     * Invalidates all cache entries that match the request.
     *
     * @param Request $request A Request instance
     */
    public function invalidate(Request $request)
    {
        $modified = false;
        $key = $this->getCacheKey($request);
        $entries = array();

        foreach ($this->getMetadata($key) as $entry) {
            $response = $this->restoreResponse($entry[1]);

            if ($response->isFresh()) {
                $response->expire();
                $modified = true;
                $entries[] = array($entry[0], $this->persistResponse($response));

                continue;
            }

            $entries[] = $entry;
        }

        if ($modified) {
            if (false === $this->memcached->set($key, serialize($entries))) {
                throw new \RuntimeException('Unable to store the metadata.');
            }
        }
    }

    /**
     * Locks the cache for a given Request.
     *
     * @param Request $request A Request instance
     *
     * @return bool true if the lock is acquired, the path to the current lock otherwise
     */
    public function lock(Request $request)
    {
        $requestKey = $this->getCacheKey($request);
        $lockKey = $this->getLockKey($requestKey);

        $locks = $this->memcached->get('http_cache_locks');
        if (false === $locks) {
            $locks = [];
        }

        $lockData = $this->memcached->get($lockKey);
        if (false !== $lockData) {
            return false;
        }

        $locks[] = $lockKey;
        $this->memcached->set('http_cache_locks', $locks);

        return $this->memcached->set($lockKey, 1);
    }

    /**
     * Releases the lock for the given Request.
     *
     * @param Request $request A Request instance
     *
     * @return bool False if the lock file does not exist or cannot be unlocked, true otherwise
     */
    public function unlock(Request $request)
    {
        $requestKey = $this->getCacheKey($request);
        $lockKey = $this->getLockKey($requestKey);

        $locks = $this->memcached->get('http_cache_locks');
        if (false === $locks) {
            $locks = [];
        }

        if (($key = array_search($lockKey, $locks)) !== false) {
            unset($locks[$key]);
        }

        $this->memcached->set('http_cache_locks', $locks);

        return $this->memcached->delete($lockKey);
    }

    /**
     * Returns whether or not a lock exists.
     *
     * @param Request $request A Request instance
     *
     * @return bool true if lock exists, false otherwise
     */
    public function isLocked(Request $request)
    {
        $requestKey = $this->getCacheKey($request);
        $lockKey = $this->getLockKey($requestKey);

        return (false !== $this->memcached->get($lockKey));
    }

    /**
     * Purges data for the given URL.
     *
     * @param string $url A URL
     *
     * @return bool true if the URL exists and has been purged, false otherwise
     */
    public function purge($url)
    {
        $requestKey = $this->getCacheKey(Request::create($url));

        return $this->memcached->delete($requestKey);
    }

    /**
     * Cleanups storage.
     */
    public function cleanup()
    {
        $locks = $this->memcached->get('http_cache_locks');
        if (empty($locks)) {
            return;
        }

        foreach ($locks as $lock) {
            $this->memcached->delete($lock);
        }

        $this->memcached->set('http_cache_locks', []);
    }

    /**
     * Gets all data associated with the given key.
     * Use this method only if you know what you are doing.
     *
     * @param string $key The store key
     *
     * @return array An array of data associated with the key
     */
    private function getMetadata($key)
    {
        $entries = $this->memcached->get($key);

        if (false === $entries) {
            return array();
        }

        return unserialize($entries);
    }

    /**
     * Determines whether two Request HTTP header sets are non-varying based on
     * the vary response header value provided.
     *
     * @param string $vary A Response vary header
     * @param array  $env1 A Request HTTP header array
     * @param array  $env2 A Request HTTP header array
     *
     * @return boolean true if the two environments match, false otherwise
     */
    private function requestsMatch($vary, $env1, $env2)
    {
        if (empty($vary)) {
            return true;
        }

        foreach (preg_split('/[\s,]+/', $vary) as $header) {
            $key = strtr(strtolower($header), '_', '-');
            $v1 = isset($env1[$key]) ? $env1[$key] : null;
            $v2 = isset($env2[$key]) ? $env2[$key] : null;

            if ($v1 !== $v2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Restores a Response from the HTTP headers and body.
     *
     * @param array $headers An array of HTTP headers for the Response
     * @param string $body The Response body
     *
     * @return Response
     */
    private function restoreResponse($headers, $body = null)
    {
        $status = $headers['X-Status'][0];
        unset($headers['X-Status']);

        if (null !== $body) {
            $headers['X-Body-Eval'] = $body;
        }

        return new Response($body, $status, $headers);
    }

    /**
     * Returns a cache key for a given request
     *
     * @param Request $request
     *
     * @return string
     */
    private function getCacheKey(Request $request)
    {
        return sprintf("md%s", hash('sha256', $request->getUri()));
    }

    /**
     * Gets the lock key for the request key
     *
     * @param string $requestKey
     *
     * @return string
     */
    private function getLockKey($requestKey)
    {
        return sprintf("lock%s", $requestKey);
    }

    /**
     * Returns content digest for response.
     *
     * @param Response $response
     *
     * @return string
     */
    protected function generateContentDigest(Response $response)
    {
        return sprintf("en%s", hash('sha256', $response->getContent()));
    }

    /**
     * Persists the Request HTTP headers.
     *
     * @param Request $request A Request instance
     *
     * @return array An array of HTTP headers
     */
    private function persistRequest(Request $request)
    {
        return $request->headers->all();
    }

    /**
     * Persists the Response HTTP headers.
     *
     * @param Response $response A Response instance
     *
     * @return array An array of HTTP headers
     */
    private function persistResponse(Response $response)
    {
        $headers = $response->headers->all();
        $headers['X-Status'] = array($response->getStatusCode());

        return $headers;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getDefaultOptions(array $options)
    {
        return array_merge(
            [
                'enabled'       => true,
                'debug'         => false,
                'persistentId'  => null,
            ],
            $options
        );
    }
}
