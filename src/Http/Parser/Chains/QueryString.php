<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains;

use Sinbadxiii\PhalconAuthJWT\Http\Parser\ParserInterface;

class QueryString implements ParserInterface
{
    use KeyTrait;

    /**
     * Try to parse the token from the request query string.
     */
    public function parse($request): ?string
    {
        return $request->get($this->key);
    }
}