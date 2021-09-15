<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers;

use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Sinbadxiii\PhalconAuthJWT\Blacklist;
use Sinbadxiii\PhalconAuthJWT\Builder;
use Sinbadxiii\PhalconAuthJWT\Guards\JWTGuard;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\AuthHeaders;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\InputSource;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\QueryString;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Parser;
use Sinbadxiii\PhalconAuthJWT\JWT;
use Sinbadxiii\PhalconAuthJWT\JWTManager;

/**
 * Class JWTProvider
 * @package Sinbadxiii\PhalconAuthJWT
 */
class JWTServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'jwt';

    public function __construct()
    {
        $this->extendAuthGuard();
    }

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $configJwt =  $di->getShared('config')->path('jwt');

        $di->set($this->providerName, function () use ($di, $configJwt) {

            $providerJwt = $configJwt->providers->jwt;

            $builder = new Builder();

            $builder->lockSubject($configJwt->lock_subject)
                ->setTTL($configJwt->ttl)
                ->setRequiredClaims($configJwt->required_claims->toArray())
                ->setLeeway($configJwt->leeway)
                ->setMaxRefreshPeriod($configJwt->max_refresh_period);

            $parser = new Parser($di->getRequest(), [
                    new AuthHeaders,
                    new QueryString,
                    new InputSource,
            ]);

            $providerStorage = $configJwt->providers->storage;

            $blacklist = new Blacklist(new $providerStorage($di->getCache()));

            $blacklist->setGracePeriod($configJwt->blacklist_grace_period);

            $manager = new JWTManager(new $providerJwt(
                $configJwt->secret,
                $configJwt->algo,
                $configJwt->keys->toArray()
            ), $blacklist, $builder);

            $manager->setBlacklistEnabled((bool) $configJwt->blacklist_enabled);

            return new JWT($builder, $manager, $parser);
        });
    }

    private function extendAuthGuard()
    {
        $auth = Di::getDefault()->getShared("auth");

        $auth->extend('jwt', function($name, $config) use ($auth) {
                return new JWTGuard($name, $auth->createUserProvider($config));
            }
        );
    }
}