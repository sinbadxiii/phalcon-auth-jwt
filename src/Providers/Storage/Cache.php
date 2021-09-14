<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers\Storage;

use Phalcon\Cache as PhalconCache;
use Sinbadxiii\PhalconAuthJWT\Contracts\Providers\Storage;

class Cache implements Storage
{
    protected PhalconCache $cache;

    protected string $tag = 'auth-jwt';

    public function __construct(PhalconCache $cache)
    {
        $this->cache = $cache;
    }

    public function add(string $key, $value, $ttl): void
    {
        $this->cache()->set($key, $value, $ttl);
    }

    public function forever(string $key, $value): void
    {
        $this->cache()->set($key, $value, -1);
    }

    public function get(string $key)
    {
        return $this->cache()->get($key);
    }

    public function has(string $key)
    {
        return $this->cache()->has($key);
    }

    public function delete(string $key): void
    {
        $this->cache()->delete($key);
    }

    public function flush(): void
    {
        $this->cache()->clear();
    }

    protected function cache(): PhalconCache
    {
        return $this->cache;
    }
}