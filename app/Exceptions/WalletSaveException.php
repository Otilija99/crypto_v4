<?php

namespace CryptoApp\Exceptions;

use Exception;

class WalletSaveException extends Exception
{
    protected $message = 'Failed to update wallet.';
}
