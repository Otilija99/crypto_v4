<?php
namespace CryptoApp\Repositories\Transaction;

use CryptoApp\Models\Transaction;


interface TransactionRepository
{
    public function getAll();
    public function save(Transaction $transaction);
}