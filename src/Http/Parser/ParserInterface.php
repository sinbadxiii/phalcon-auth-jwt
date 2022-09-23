<?php

namespace Sinbadxiii\PhalconAuthJWT\Http\Parser;

interface ParserInterface
{
    /**
     * Parse the request.
     */
    public function parse($request): ?string;
}
