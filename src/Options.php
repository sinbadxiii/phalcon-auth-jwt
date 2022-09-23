<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Phalcon\Support\Helper\Arr\Get;

final class Options
{
    const LEEWAY = 'leeway';
    const REQUIRED_CLAIMS = 'required_claims';
    const MAX_REFRESH_PERIOD = 'max_refresh_period';
    const VALIDATORS = 'validators';

    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function requiredClaims(): array
    {
        return (new Get())($this->options, self::REQUIRED_CLAIMS, []);
    }

    public function leeway(): int
    {
        return (new Get())($this->options, self::LEEWAY, 0);
    }

    public function maxRefreshPeriod(): ?int
    {
        return (new Get())($this->options, self::MAX_REFRESH_PERIOD);
    }

    public function validators(): array
    {
        return (new Get())($this->options, self::VALIDATORS, []);
    }
}