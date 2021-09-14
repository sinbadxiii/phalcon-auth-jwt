<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Contracts\Providers;

interface Storage
{
    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     *
     * @return void
     */
    public function add(string $key, $value, $minutes): void;

    /**
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever(string $key, $value): void;

    /**
     * @param  string  $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param  string  $key
     *
     * @return boolean
     */
    public function has(string $key);

    /**
     * @param  string  $key
     *
     * @return bool
     */
    public function delete(string $key): void;

    /**
     * @return void
     */
    public function flush(): void;
}