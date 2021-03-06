<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

class Custom extends Claim
{
    const KEY = 'custom';

    public function __construct(string $name, $value)
    {
        parent::__construct($value);
        $this->setName($name);
    }
}