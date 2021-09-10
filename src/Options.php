<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT;

use Phalcon\Helper\Arr;

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
        return Arr::get($this->options, static::REQUIRED_CLAIMS, []);
    }

    public function leeway(): int
    {
        return Arr::get($this->options, static::LEEWAY, 0);
    }

    public function maxRefreshPeriod(): ?int
    {
        return Arr::get($this->options, static::MAX_REFRESH_PERIOD);
    }

    public function validators(): array
    {
        return Arr::get($this->options, static::VALIDATORS, []);
    }
}