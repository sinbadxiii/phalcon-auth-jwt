<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Providers\JWT\Phalcon;

use Phalcon\Security\JWT\Builder as PhalconBuilder;
use Phalcon\Support\Version;

class Builder extends PhalconBuilder
{
    protected const FIFTH_VERSION = 5;

    /**
     * @param string $name
     * @param $value
     * @return PhalconBuilder
     */
    public function withClaim(string $name, $value): PhalconBuilder
    {
        try {
            $version = (new Version)->getPart(Version::VERSION_MAJOR);

            return $this->addClaim($name, $value);
        } catch (\Throwable $e) {
            if ($e->getMessage() === 'Class "Phalcon\Version" not found') {
                return $this->setClaim($name, $value);
            }
        }
    }
}
