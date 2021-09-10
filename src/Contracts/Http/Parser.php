<?php

namespace Sinbadxiii\PhalconAuthJWT\Contracts\Http;

interface Parser
{
    /**
     * Parse the request.
     */
    public function parse($request): ?string;
}
