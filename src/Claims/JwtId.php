<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use Phalcon\Helper\Str;
use Sinbadxiii\PhalconAuthJWT\Contracts\Claim as ClaimContract;

class JwtId extends Claim
{
    const NAME = 'jti';

    public static function make($value = null): ClaimContract
    {
        return new static($value ?? Str::random(16));
    }
}
