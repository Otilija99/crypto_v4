<?php

namespace CryptoApp\Repositories\Wallet;

use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Exceptions\WalletUpdateException;
use CryptoApp\Models\Wallet;
use Medoo\Medoo;
use Exception;

class SqliteWalletRepository implements WalletRepository
{
    private Medoo $database;

    public function __construct()
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => 'storage/database.sqlite',
        ]);

        $this->createTables();
    }

    private function createTables(): void
    {
        try {
            $this->database->exec('CREATE TABLE IF NOT EXISTS wallets (
                id VARCHAR(225) PRIMARY KEY,
                user_id VARCHAR(225) NOT NULL,
                balance REAL NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users (id)
            )');
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function saveWallet(Wallet $wallet): void
    {
        try {
            $this->database->insert('wallets', [
                'id' => $wallet->getId(),
                'user_id' => $wallet->getUserId(),
                'balance' => $wallet->getBalance(),
            ]);
        } catch (Exception $e) {
            throw new UserSaveException("Failed to save wallet: " . $e->getMessage());
        }
    }

    public function getWallet(string $userId): ?array
    {
        try {
            $walletData = $this->database->get('wallets', '*', ['user_id' => $userId]);
            return $walletData ?: null;
        } catch (Exception $e) {
            throw new WalletNotFoundException("Wallet not found: " . $e->getMessage());
        }
    }

    public function updateWallet(Wallet $wallet): void
    {
        try {
            $this->database->update('wallets', [
                'balance' => $wallet->getBalance(),
            ], [
                'id' => $wallet->getId(),
            ]);
        } catch (Exception $e) {
            throw new WalletUpdateException("Failed to update wallet: " . $e->getMessage());
        }
    }

    public function getTransactionsByUserId(string $userId): array
    {
        try {
            return $this->database->select('transactions', '*', ['user_id' => $userId]);
        } catch (Exception $e) {
            throw new TransactionGetException("Failed to get transactions: " . $e->getMessage());
        }
    }
}
