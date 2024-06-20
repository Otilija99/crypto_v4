<?php

namespace CryptoApp\Exceptions;

use Exception;

class InvalidSymbolFormatException extends Exception
{
    protected $message = 'Invalid symbol format.';
}