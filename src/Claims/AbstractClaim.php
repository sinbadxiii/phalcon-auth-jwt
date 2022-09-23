<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Claims;

use JsonSerializable;

abstract class AbstractClaim implements ClaimInterface, JsonSerializable
{
    /**
     * The claim name.
     */
    protected ?string $name = null;

    /**
     * The claim value.
     *
     * @var mixed
     */
    private $value;

    /**
     * Constructor.
     */
    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function setValue($value): ClaimInterface
    {
        $this->value = $this->validateCreate($value);

        return $this;
    }

    /**
     * Get the claim value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the claim name.
     */
    public function setName(string $name): ClaimInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the claim name.
     */
    public function getName(): string
    {
        return $this->name ?? static::NAME;
    }

    /**
     * Validate the claim for creation.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function validateCreate($value)
    {
        return $value;
    }

    /**
     * Check the claim when verifying the validity of the payload.
     */
    public function verify(): void
    {
        //
    }

    /**
     * Create an instance of the claim.
     */
    public static function make($value = null): ClaimInterface
    {
        return new static($value);
    }

    /**
     * Checks if the value matches the claim.
     *
     * @param  mixed  $value
     */
    public function matches($value, bool $strict = true): bool
    {
        return $strict
            ? $this->value === $value
            : $this->value == $value;
    }

    /**
     * Checks if the name matches the claim.
     */
    public function matchesName(string $name): bool
    {
        return $this->getName() === $name;
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Build a key value array comprising of the claim name and value.
     */
    public function toArray(): array
    {
        return [$this->getName() => $this->getValue()];
    }

    /**
     * Get the claim as JSON.
     *
     * @param  int  $options
     */
    public function toJson($options = JSON_UNESCAPED_SLASHES): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the payload as a string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}