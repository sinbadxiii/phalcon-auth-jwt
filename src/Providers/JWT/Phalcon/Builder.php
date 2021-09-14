<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers\JWT\Phalcon;

use Phalcon\Security\JWT\Builder as PhalconBuilder;

class Builder extends PhalconBuilder
{
    public function setCustom(string $name, $value): PhalconBuilder
    {
        return $this->setClaim($name, $value);
    }
}