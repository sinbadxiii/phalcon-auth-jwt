<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use Phalcon\Support\Helper\Str\Random;

class JwtId extends AbstractClaim
{
    const NAME = 'jti';

    public static function make($value = null): ClaimInterface
    {
        $random = new Random();
        return new static($value ?? $random(16));
    }
}
