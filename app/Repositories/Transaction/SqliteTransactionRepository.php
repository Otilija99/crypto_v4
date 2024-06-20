<?php

namespace CryptoApp\Repositories\Transaction;

use CryptoApp\Exceptions\TransactionGetException;
use CryptoApp\Exceptions\TransactionSaveException;
use CryptoApp\Models\Transaction;
use Exception;
use Medoo\Medoo;
class SqliteTransactionRepository implements TransactionRepository
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
    $this->database->exec('CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT NOT NULL,
                symbol TEXT NOT NULL,
                amount REAL NOT NULL,
                price REAL NOT NULL,
                timestamp INTEGER NOT NULL
                                        )');
} catch (Exception $e) {
echo"Error: " . $e->getMessage();
}
}
    public function getAll(): array
    {
        $transactions = [];

        try {
            $transactionsData = $this->database->select(
                'transactions',
                ['id', 'type', 'symbol', 'amount', 'price', 'timestamp']
            );

            foreach ($transactionsData as $data) {
                $transactions[] = Transaction::fromArray($data);
            }
        } catch (Exception $e) {
            throw new TransactionGetException("Failed to get transactions: " . $e->getMessage());
        }

        return $transactions;
    }

    public function save (Transaction $transaction): void
    {
        try{
            $this->database->insert(
                'transactions', [
                'type' => $transaction->getType(),
                'symbol' => strtoupper($transaction->getSymbol()),
                'amount' => $transaction->getAmount(),
                'price' => $transaction->getPrice(),
                'timestamp' => $transaction->getTimestamp()->format('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            throw new TransactionSaveException("Failed to save transaction: " . $e->getMessage());
        }
    }}
