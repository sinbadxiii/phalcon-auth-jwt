<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use Sinbadxiii\PhalconAuthJWT\Contracts\Claim as ClaimContract;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException;

class NotBefore extends Claim
{
    use DatetimeTrait;

    const NAME = 'nbf';

    public function verify(): void
    {
        if ($this->isFuture($this->getValue())) {
            throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future');
        }
    }

    public static function make($value = null): ClaimContract
    {
        return new static($value ?? date("U"));
    }
}