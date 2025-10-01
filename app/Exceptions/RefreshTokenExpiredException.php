<?php

namespace App\Exceptions;

use Exception;

class RefreshTokenExpiredException extends Exception
{
    protected $code = 401;

    public function __construct()
    {
        parent::__construct(__('auth.invalid_token'));
    }
}
