<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use Sinbadxiii\PhalconAuthJWT\Exceptions\TokenExpiredException;

class Expiration extends AbstractClaim
{
    use DatetimeTrait;

    /**
     * @var string
     */
    const NAME = 'exp';

    /**
     * {@inheritdoc}
     */
    public function verify(): void
    {
        if ($this->isPast($this->getValue())) {
            throw new TokenExpiredException('Token has expired');
        }
    }
}