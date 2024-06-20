<?php


namespace CryptoApp\Exceptions;

use Exception;

class TransactionGetException extends Exception
{
    protected $message = 'Failed to get transactions.';
}