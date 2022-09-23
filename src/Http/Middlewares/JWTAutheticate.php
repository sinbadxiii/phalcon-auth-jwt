<?php

namespace Sinbadxiii\PhalconAuthJWT\Http\Middlewares;

use Sinbadxiii\PhalconAuth\Access\Authenticate;

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
        $this->checkForToken();

        try {
            $this->auth->parseToken()->checkOrFail();
        } catch (\Throwable $e) {
            $this->message = ['status' => $e->getMessage()];
            $this->redirectTo();
        }

        return true;
    }

    public function checkForToken()
    {
        if (! $this->auth->parser()->hasToken()) {
            $this->message = ['status' => 'Token not provided'];
            $this->redirectTo();
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