<?php

namespace CryptoApp\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = 'Insufficient balance to complete the transaction.';
}