<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use Sinbadxiii\PhalconAuthJWT\Exceptions\InvalidClaimException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenExpiredException;
use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenInvalidException;
use Sinbadxiii\PhalconAuthJWT\Support\Helpers;

class IssuedAt extends AbstractClaim
{
    use DatetimeTrait {
        validateCreate as commonValidateCreate;
    }

    /**
     * @var string
     */
    const NAME = 'iat';

    /**
     * {@inheritdoc}
     */
    public function validateCreate($value)
    {
        $this->commonValidateCreate($value);

        if ($this->isFuture($value)) {
            throw new InvalidClaimException($this);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(): void
    {
        if ($this->isFuture($this->getValue())) {
            throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future');
        }

        if ($this->maxRefreshPeriod !== null) {
            if (Helpers::timestamp($this->getValue())->addMinutes($this->maxRefreshPeriod)->isFuture()) {
                throw new TokenExpiredException('Token has expired');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function make($value = null): ClaimInterface
    {
        return new static($value ?? Helpers::now());
    }
}