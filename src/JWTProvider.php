<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\AuthHeaders;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\InputSource;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains\QueryString;
use Sinbadxiii\PhalconAuthJWT\Http\Parser\Parser;

/**
 * Class JWTProvider
 * @package Sinbadxiii\PhalconAuthJWT
 */
class JWTProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'jwt';

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $configJwt =  $di->getShared('config')->path('jwt');

        $di->setShared($this->providerName, function () use ($di, $configJwt) {

            $provider = $configJwt->providers->jwt;

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

            /**
             * @todo доделать блэклисты
             */
            $blacklist = [];

            $manager = new JWTManager(new $provider(
                $configJwt->secret,
                $configJwt->algo,
                $configJwt->keys->toArray()
            ), $blacklist, $builder);

            $manager->setBlacklistEnabled((bool) $configJwt->blacklist_enabled);

            return new JWT($builder, $manager, $parser);
        });
    }
}