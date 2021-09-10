<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains;

use Sinbadxiii\PhalconAuthJWT\Contracts\Http\Parser as ParserContract;

class InputSource implements ParserContract
{
    use KeyTrait;

    /**
     * Try to parse the token from the request input source.
     */
    public function parse($request): ?string
    {
        if (!empty($param = $request->get($this->key))) {
            return $param;
        }

        if (!empty($jsonRawBody = $request->getJsonRawBody()) && isset($jsonRawBody->{$this->key})) {
            return $jsonRawBody->{$this->key};
        }

        return $request->getPost($this->key);
    }
}