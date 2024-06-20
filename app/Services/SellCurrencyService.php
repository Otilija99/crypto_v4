<?php

namespace CryptoApp\Services;

use CryptoApp\Api\ApiClientInterface;
use CryptoApp\Database\DatabaseInterface;
use CryptoApp\Exceptions\InsufficientCryptoAmountException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Models\Transaction;
use CryptoApp\Models\Wallet;
use Carbon\Carbon;
use Exception;

class SellCurrencyService {

    private ApiClientInterface $apiClient;
    private DatabaseInterface $database;
    private WalletService $walletService;
    private string $userId;

    public function __construct(
        ApiClientInterface $apiClient,
        DatabaseInterface $database,
        WalletService $walletService,
        string $userId
    )
    {
        $this->apiClient = $apiClient;
        $this->database = $database;
        $this->walletService = $walletService;
        $this->userId = $userId;
    }

    public function execute (string $symbol, float $amount): void
    {
        try {
            $user = $this->database->getUserById($this->userId);
            if (!$user) {
                throw new UserNotFoundException("User with ID {$this->userId} not found.");
            }

            $currency = $this->apiClient->searchCryptoCurrencies($symbol);
            $wallet = $this->database->getWallet($this->userId);
            if (!$wallet) {
                throw new WalletNotFoundException("User's wallet not found.");
            }
            $currentBalance = $wallet['balance'];
            $existingAmount = $this->walletService->getExistingAmountInWallet($symbol);

            if ($existingAmount < $amount) {
                throw new InsufficientCryptoAmountException("User does not have enough $symbol to sell.");
            }
            $timestamp = Carbon::now();
            $transaction = new Transaction(
                $this->userId,
                'sell',
                $symbol,
                $amount,
                $currency->getPrice(),
                $timestamp,
            );
            $this->database->saveTransaction($transaction);

            $newBalance = $currentBalance + ($currency->getPrice() * $amount);
            $updatedWallet = new Wallet($this->userId, $newBalance);
            $this->database->updateWallet($updatedWallet);

            echo "Successfully sold $amount $symbol.\n";

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientCryptoAmountException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "An unexpected error occurred: " . $e->getMessage();
        }
    }
}