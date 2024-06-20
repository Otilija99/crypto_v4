<?php

namespace CryptoApp\Services;

use CryptoApp\Storage\StorageInterface;
use CryptoApp\Models\Transaction;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use Exception;

class BuyCurrencyService
{

    private CurrencyRepository $currencyRepository;
    private DatabaseInterface $database;
    private string $userId;

    public function __construct(
        CurrencyRepository $currencyRepository,
        StorageInterface $storage, //Transaction Repository
    )
    {
        $this->currenyRepository = $currencyRepository;
        $this->storage=$storage;
    }

    public function execute(string $symbol, float $amount): void
    {
        try {
            $currency = $this->currencyRepository->search($symbol);
            $totalCost = $currency->getPrice() * $amount;
            $wallet = $this->database->getWallet($this->userId);
            if (!$wallet) {
                throw new WalletNotFoundException("User's wallet not found.");
            }
            if ($wallet['balance'] < $totalCost) {
                throw new InsufficientBalanceException(
                    "Insufficient balance to buy $symbol. Required: $totalCost"
                );
            }
            $currentBalance = $wallet['balance'];

            $timestamp = Carbon::now();
            $transaction = new Transaction(
                $this->userId,
                'buy',
                $symbol,
                $amount,
                $currency->getPrice(),
                $timestamp,
            );

            $this->database->saveTransaction($transaction);

            $newBalance = $currentBalance - ($currency->getPrice() * $amount);
            $updatedWallet = new Wallet($this->userId, $newBalance);
            $this->database->updateWallet($updatedWallet);

            echo "Successfully bought $amount $symbol.\n";

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientBalanceException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "An unexpected error occurred: " . $e->getMessage();
        }
    }
}