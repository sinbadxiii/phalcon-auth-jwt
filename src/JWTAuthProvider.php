<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Sinbadxiii\PhalconAuthJWT\Guards\JWTGuard;

/**
 * Class JWTAuthProvider
 * @package Sinbadxiii\PhalconAuthJWT
 */
class JWTAuthProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $auth = $di->getShared("auth");

        $auth->extend('jwt', function($name, $config) use ($auth) {
                return new JWTGuard($name, $auth->createUserProvider($config));
            }
        );
    }
}