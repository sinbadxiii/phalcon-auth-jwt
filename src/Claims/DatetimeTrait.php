<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use DateInterval;
use DateTimeInterface;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Sinbadxiii\PhalconAuthJWT\Exceptions\InvalidClaimException;
use Sinbadxiii\PhalconAuthJWT\Support\Helpers;

trait DatetimeTrait
{
    /**
     * Time leeway in seconds.
     */
    protected int $leeway = 0;

    /**
     * Max refresh period in minutes.
     */
    protected ?int $maxRefreshPeriod = null;

    public function setValue($value): ClaimInterface
    {
        if ($value instanceof DateInterval) {
            $value = date("NOW");
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->getTimestamp();
        }

        return parent::setValue($value);
    }

    public function validateCreate($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidClaimException($this);
        }

        return $value;
    }

    protected function isFuture(int $value): bool
    {
        return Helpers::is_future($value, $this->leeway);
    }

    protected function isPast(int $value): bool
    {
        return Helpers::is_past($value, $this->leeway);
    }

    public function setLeeway(int $leeway): self
    {
        $this->leeway = $leeway;

        return $this;
    }

    public function getLeeway(): int
    {
        return $this->leeway;
    }

    public function setMaxRefreshPeriod(?int $period): self
    {
        $this->maxRefreshPeriod = $period;

        return $this;
    }

    public function getMaxRefreshPeriod(): ?int
    {
        return $this->maxRefreshPeriod;
    }

    /**
     * Get the claim value as a Carbon instance.
     */
    public function asCarbon(): Carbon
    {
        return Helpers::timestamp($this->getValue());
    }

    /**
     * Get the claim value as a CarbonInterval instance.
     */
    public function asCarbonInterval(): CarbonInterval
    {
        return Helpers::now()
            ->diffAsCarbonInterval($this->asCarbon()->endOfSecond())
            ->microseconds(0);
    }
}