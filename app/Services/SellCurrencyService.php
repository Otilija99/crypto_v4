<?php

namespace CryptoApp\Services;

use CryptoApp\Models\Transaction;
use CryptoApp\Models\Wallet;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Repositories\User\UserRepository;
use CryptoApp\Repositories\Wallet\WalletRepository;
use CryptoApp\Exceptions\InsufficientCryptoAmountException;
use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\WalletNotFoundException;
use Carbon\Carbon;
use Exception;

class SellCurrencyService
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
        UserRepository $userRepository,
        string $userId
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
        $this->userId = $userId;
    }

    public function execute(string $symbol, float $amount): void
    {
        try {

            $user = $this->userRepository->findById($this->userId);
            if (!$user) {
                throw new UserNotFoundException("User with ID {$this->userId} not found.");
            }


            $currency = $this->currencyRepository->findBySymbol($symbol);


            $wallet = $this->walletRepository->findByUserId($this->userId);
            if (!$wallet) {
                throw new WalletNotFoundException("User's wallet not found.");
            }


            $existingAmount = $this->walletRepository->getAmountInWallet($this->userId, $symbol);
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
                $timestamp
            );
            $this->transactionRepository->save($transaction);


            $currentBalance = $wallet->getBalance();
            $newBalance = $currentBalance + ($currency->getPrice() * $amount);
            $wallet->setBalance($newBalance);
            $this->walletRepository->update($wallet);

            echo "Successfully sold $amount $symbol.\n";

        } catch (UserNotFoundException|WalletNotFoundException|InsufficientCryptoAmountException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "An unexpected error occurred: " . $e->getMessage();
        }
    }
}
