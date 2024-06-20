<?php

namespace CryptoApp\Exceptions;

use Exception;

class TransactionFailedException extends Exception
{
    protected $message = 'Transaction failed.';
}