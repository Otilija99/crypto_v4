<?php

namespace CryptoApp\Exceptions;

use Exception;

class InsufficientCryptoAmountException extends Exception
{
    protected $message = 'Insufficient cryptocurrency amount to sell.';
}