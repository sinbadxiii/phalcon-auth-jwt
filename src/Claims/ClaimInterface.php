<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

interface ClaimInterface
{
    public function setValue($value): self;

    public function getValue();

    public function setName(string $name): self;

    public function getName(): string;

    public function validateCreate($value);
}