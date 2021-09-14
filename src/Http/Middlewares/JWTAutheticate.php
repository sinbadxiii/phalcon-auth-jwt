<?php

namespace Sinbadxiii\PhalconAuthJWT\Http\Middlewares;

use Sinbadxiii\PhalconAuth\Middlewares\Authenticate;
use Sinbadxiii\PhalconAuthJWT\Exceptions\UnauthorizedHttpException;

/**
 * Class JWTAutheticate
 * @package Sinbadxiii\PhalconAuthJWT\Http\Middlewares
 */
class JWTAutheticate extends Authenticate
{
    /**
     * @var array
     */
    protected array $message;

    public function redirectTo()
    {
        $this->response();
    }

    public function authenticate()
    {
        if (! $this->isGuest()) {

            $this->checkForToken();

            try {
                $this->auth->parseToken()->checkOrFail();
            } catch (\Throwable $e) {
                $this->message = ['status' => $e->getMessage()];
                $this->unauthenticated();
            }
        }

        return true;
    }

    public function checkForToken()
    {
        if (! $this->auth->parser()->hasToken()) {
            $this->message = ['status' => 'Token not provided'];
            $this->unauthenticated();
        }
    }

    private function getMessage()
    {
        return $this->message;
    }

    private function response()
    {
        $this->response->setStatusCode(401, 'Unauthorized');
        $this->response->setJsonContent($this->getMessage());
        if (!$this->response->isSent()) {
            $this->response->send();
        }
        exit;
    }
}