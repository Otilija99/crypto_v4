<?php

namespace CryptoApp\Services;

use CryptoApp\Models\Transaction;
use CryptoApp\Models\Wallet;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Repositories\User\UserRepository;
use CryptoApp\Repositories\Wallet\WalletRepository;
use Exception;
use Carbon\Carbon;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\WalletNotFoundException;
use CryptoApp\Exceptions\InsufficientBalanceException;

class BuyCurrencyService
{
    private CurrencyRepository $currencyRepository;
    private TransactionRepository $transactionRepository;
    private WalletRepository $walletRepository;
    private UserRepository $userRepository;
    private string $userId;

    public function __construct(
        CurrencyRepository $currencyRepository,
        TransactionRepository $transactionRepository,
        WalletRepository $walletRepository,
        UserRepository $userRepository
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function execute(string $symbol, float $amount): void
    {
        try {
            $currency = $this->currencyRepository->search($symbol);
            $totalCost = $currency->getPrice() * $amount;
            $wallet = $this->walletRepository->getWallet($this->userId);

            if (!$wallet) {
                throw new WalletNotFoundException("User's wallet not found.");
            }

            if ($wallet->getBalance() < $totalCost) {
                throw new InsufficientBalanceException(
                    "Insufficient balance to buy $symbol. Required: $totalCost"
                );
            }

            $currentBalance = $wallet->getBalance();
            $timestamp = Carbon::now();

            $transaction = new Transaction(
                $this->userId,
                'buy',
                $symbol,
                $amount,
                $currency->getPrice(),
                $timestamp
            );

            $this->transactionRepository->saveTransaction($transaction);

            $newBalance = $currentBalance - ($currency->getPrice() * $amount);
            $wallet->setBalance($newBalance);
            $this->walletRepository->updateWallet($wallet);

            echo "Successfully bought $amount $symbol.\n";

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientBalanceException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "An unexpected error occurred: " . $e->getMessage();
        }
    }
}
