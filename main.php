<?php

require_once 'vendor/autoload.php';

use CryptoApp\Services\BuyCurrencyService;
use CryptoApp\Services\SellCurrencyService;
use CryptoApp\Services\UserService;
use CryptoApp\Services\WalletService;
use CryptoApp\Models\Wallet;
use CryptoApp\Repositories\Currency\CoinPaprikaApiCurrencyRepository;
use CryptoApp\Repositories\User\SqliteUserRepository;
use CryptoApp\Repositories\Wallet\SqliteWalletRepository;
use CryptoApp\Exceptions\TransactionGetException;
use CryptoApp\Exceptions\TransactionFailedException;
use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Instantiate repositories
$currencyRepository = new CoinPaprikaApiCurrencyRepository();
$userRepository = new SqliteUserRepository();
$walletRepository = new SqliteWalletRepository();

// Instantiate services
$userService = new UserService($userRepository);

$userId = null;

// Main menu for registration and login
while (true) {
    echo "\n\033[1m\033[4mCRYPTO CURRENCY APP\033[0m\n\n";
    echo "1. Register\n";
    echo "2. Login\n";
    echo "3. Exit\n";
    echo "\n";
    echo "Enter the number of your choice: ";
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case 1:
            $username = trim(readline("Enter username: "));
            $password = trim(readline("Enter password: "));
            $userService->register($username, $password);
            echo "User registered successfully.\n";
            break;

        case 2:
            $username = trim(readline("Enter username: "));
            $password = trim(readline("Enter password: "));
            try {
                $user = $userService->login($username, $password);
                $userId = $user->getId();
                echo "Welcome, " . $user->getUsername() . "!\n";
            } catch (Exception $e) {
                echo "Invalid username or password. Please try again.\n";
            }
            break;

        case 3:
            exit;

        default:
            echo "Invalid choice. Please try again.\n";
            break;
    }

    if ($userId !== null) {
        break;
    }
}

// Initialize wallet
$initialBalance = 1000.0;
$walletData = $walletRepository->getWallet($userId);
if (!$walletData) {
    $wallet = new Wallet($userId, $initialBalance);
    $walletRepository->saveWallet($wallet);
    $walletData = $walletRepository->getWallet($userId);
}
$wallet = new Wallet($walletData['user_id'], $walletData['balance']);

// Instantiate wallet and transaction services
$walletService = new WalletService($wallet, $walletRepository, $userId);
$buyService = new BuyCurrencyService($currencyRepository, $walletRepository, $userId);
$sellService = new SellCurrencyService($currencyRepository, $walletRepository, $walletService, $userId);

// Display the main app menu
echo "\n\033[1m\033[4mCRYPTO CURRENCY APP\033[0m\n\n";

echo "Your current wallet state:\n";
$walletService->displayWalletState();

while (true) {
    echo "\n";
    echo "1. Display TOP 10 crypto currencies\n";
    echo "2. Search for crypto currency using its symbol\n";
    echo "3. Buy crypto currency\n";
    echo "4. Sell crypto currency\n";
    echo "5. Display list of transactions\n";
    echo "6. Display current state of Wallet\n";
    echo "7. Exit\n";
    echo "\n";
    echo "Enter the number of your choice: ";
    $choice = trim(fgets(STDIN));

    try {
        switch ($choice) {
            case 1:
                $topCryptos = $currencyRepository->getTop();
                $output = new ConsoleOutput();
                $table = new Table($output);
                $table->setHeaders(['Rank', 'Name', 'Symbol', 'Price']);

                foreach ($topCryptos as $crypto) {
                    $table->addRow([
                        $crypto->getRank(),
                        $crypto->getName(),
                        $crypto->getSymbol(),
                        number_format($crypto->getPrice(), 8),
                    ]);
                }
                $table->render();
                break;

            case 2:
                $symbol = strtoupper(trim(readline("Enter the symbol: ")));
                $currencyInfo = $currencyRepository->search($symbol);
                echo "Currency Name: " . $currencyInfo->getName() . "\n";
                echo "Currency Symbol: " . $currencyInfo->getSymbol() . "\n";
                echo "Current Price (USD): " . number_format($currencyInfo->getPrice(), 8) . "\n";
                break;

            case 3:
                $symbol = strtoupper(trim(readline("Enter the symbol to buy: ")));
                $amount = floatval(trim(readline("Enter the amount to buy: ")));
                $balance = $walletService->getBalance();
                $cryptoData = $currencyRepository->search($symbol);
                $price = $cryptoData->getPrice();
                $totalCost = $price * $amount;

                if ($balance >= $totalCost) {
                    $buyService->execute($symbol, $amount);
                    echo "Purchase successful.\n";
                } else {
                    echo "\033[31mInsufficient balance. Please try again with a lower amount.\033[0m\n";
                }
                break;

            case 4:
                $symbol = strtoupper(trim(readline("Enter the symbol to sell: ")));
                $amount = floatval(trim(readline("Enter the amount to sell: ")));
                $sellService->execute($symbol, $amount);
                echo "Sale successful.\n";
                break;

            case 5:
                $output = new ConsoleOutput();
                $table = new Table($output);
                $table->setHeaders(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp']);

                $transactions = $walletRepository->getTransactionsByUserId($userId);
                foreach ($transactions as $transaction) {
                    $table->addRow([
                        $transaction->getType(),
                        $transaction->getSymbol(),
                        $transaction->getAmount(),
                        number_format($transaction->getPrice(), 8),
                        $transaction->getTimestamp()->format('Y-m-d H:i:s'),
                    ]);
                }
                $table->render();
                break;

            case 6:
                $walletService->displayWalletState();
                break;

            case 7:
                exit;

            default:
                echo "Invalid choice. Please try again.\n";
                break;
        }
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage() . "\n";
    }
}
