<?php

namespace CryptoApp\Exceptions;

use Exception;

class WalletUpdateException extends Exception
{
    protected $message = 'Failed to save wallet.';}