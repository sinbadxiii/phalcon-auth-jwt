<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Http\Parser\Chains;

use Sinbadxiii\PhalconAuthJWT\Http\Parser\ParserInterface;

class AuthHeaders implements ParserInterface
{
    /**
     * The header name.
     */
    protected string $header = 'Authorization';

    /**
     * The header prefix.
     */
    protected string $prefix = 'Bearer';

    /**
     * Try to parse the token from the request header.
     */
    public function parse($request): ?string
    {
        $header = $request->getHeader($this->header)
            ?: $this->fromAltHeaders($request);

        if ($header && preg_match('/'.$this->prefix.'\s*(\S+)\b/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Set the header name.
     */
    public function setHeaderName(string $headerName): self
    {
        $this->header = $headerName;

        return $this;
    }

    /**
     * Set the header prefix.
     */
    public function setHeaderPrefix(string $headerPrefix): self
    {
        $this->prefix = $headerPrefix;

        return $this;
    }

    /**
     * Attempt to parse the token from some other possible headers.
     */
    protected function fromAltHeaders($request): ?string
    {
        return $request->getServer('HTTP_AUTHORIZATION')
            ?? $request->getServer('REDIRECT_HTTP_AUTHORIZATION');
    }
}