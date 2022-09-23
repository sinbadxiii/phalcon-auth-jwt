<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

class Custom extends AbstractClaim
{
    const NAME = 'custom';

    public function __construct(string $name, $value)
    {
        parent::__construct($value);
        $this->setName($name);
    }
}