<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Sinbadxiii\PhalconAuthJWT\Validators\TokenValidator;

class Token
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = TokenValidator::check($value);
    }

    public function get(): string
    {
        return $this->value;
    }

    public function payload(bool $checkBlacklist = true): Payload
    {
        return Manager::decode($this, $checkBlacklist);
    }

    public function matches($token): bool
    {
        return (string) $this->get() === (string) $token;
    }

    public function __toString(): string
    {
        return $this->get();
    }
}