<?php

namespace Sinbadxiii\PhalconAuthJWT;

interface JWTSubject
{
    public function getJWTIdentifier();
    public function getJWTCustomClaims();
}
