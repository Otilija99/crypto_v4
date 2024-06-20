<?php

namespace CryptoApp\Exceptions;

use Exception;

class UserSaveException extends Exception
{
    protected $message = 'Failed to save user.';
}