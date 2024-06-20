<?php

namespace CryptoApp\Exceptions;

use Exception;

class UserLoginException extends Exception
{
    protected $message = 'User login failed.';
}
