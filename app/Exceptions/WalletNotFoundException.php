<?php

namespace CryptoApp\Exceptions;

use Exception;

class WalletNotFoundException extends Exception
{
    protected $message = 'Wallet not found.';
}