<?php

use Sinbadxiii\PhalconAuthJWT\Claims;

return [
    'secret' => $_ENV['JWT_SECRET'],

    'keys' => [
        'public' => $_ENV['JWT_PUBLIC_KEY'],
        'private' => $_ENV['JWT_PRIVATE_KEY'],
        'passphrase' => $_ENV['JWT_PASSPHRASE'],
    ],

    //default 30
    'ttl' => $_ENV['JWT_TTL'],

    //default null
    'max_refresh_period' => $_ENV['JWT_MAX_REFRESH_PERIOD'],

    //default HS256
    'algo' => $_ENV['JWT_ALGO'],

    'required_claims' => [
        Claims\Issuer::NAME,
        Claims\IssuedAt::NAME,
        Claims\Expiration::NAME,
        Claims\Subject::NAME,
        Claims\JwtId::NAME,
    ],

    'lock_subject' => true,

    //default 0
    'leeway' => $_ENV['JWT_LEEWAY'],

    //default true
    'blacklist_enabled' => $_ENV['JWT_BLACKLIST_ENABLED'],

    //default 0
    'blacklist_grace_period' => $_ENV['JWT_BLACKLIST_GRACE_PERIOD'],

    'decrypt_cookies' => false,

    'providers' => [
        /**
         * \Sinbadxiii\PhalconAuthJWT\Providers\Phalcon::class,
         * \Sinbadxiii\PhalconAuthJWT\Providers\Lcobucci::class,
         */
        'jwt' => \Sinbadxiii\PhalconAuthJWT\Providers\Lcobucci::class,
    ],
];