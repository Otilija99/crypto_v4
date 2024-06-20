<?php

namespace CryptoApp\Exceptions;

use Exception;

class TransactionSaveException extends Exception
{
    protected $message = 'Failed to save transaction.';
}