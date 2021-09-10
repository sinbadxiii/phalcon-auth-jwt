<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Support;

trait CustomClaims
{
    /**
     * Custom claims.
     */
    protected array $customClaims = [];

    /**
     * Set the custom claims.
     */
    public function customClaims(array $customClaims): self
    {
        $this->customClaims = $customClaims;

        return $this;
    }

    /**
     * Alias to set the custom claims.
     */
    public function claims(array $customClaims): self
    {
        return $this->customClaims($customClaims);
    }

    /**
     * Get the custom claims.
     */
    public function getCustomClaims(): array
    {
        return $this->customClaims;
    }
}