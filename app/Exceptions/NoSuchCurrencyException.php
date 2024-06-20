<?php

namespace CryptoApp\Exceptions;

use Exception;

class NoSuchCurrencyException extends Exception
{
    protected $message = 'Wallet not found.';
}