<?php

require_once 'vendor/autoload.php';

use CryptoApp\Services\BuyCurrencyService;
use CryptoApp\Services\SellCurrencyService;
use CryptoApp\Services\UserService;
use CryptoApp\Services\WalletService;
use CryptoApp\Models\Wallet;
use CryptoApp\Exceptions\TransactionGetException;
use CryptoApp\Exceptions\TransactionFailedException;
use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
/** @var CurrencyRepository $currencyRepository */
$currencyRepository = new \CryptoApp\Repositories\Currency\CoinPaprikaApiCurrencyRepository();

$storage = new SqliteStorage();

$wallet = new Wallet(1000, $storage);


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
            $userService->createUser($username, $password);
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

$initialBalance = 1000.0;
$walletData = $wallet->getWallet($userId);
if (!$walletData) {
    $wallet = new Wallet($userId, $initialBalance);
    $database->saveWallet($wallet);
    $walletData = $database->getWallet($userId);
}
$wallet = new Wallet($walletData['user_id'], $walletData['balance']);


$walletService = new WalletService($wallet, $database, $api, $userId);
$buyService = new BuyCurrencyService($api, $database, $userId);
$sellService = new SellCurrencyService($api, $database, $walletService, $userId);

echo "\n\033[1m\033[4mCRYPTO CURRENCY APP\033[0m\n\n";

echo "Your state of wallet at the moment:\n";
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

    switch ($choice) {
        case 1:
            try {
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
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage() . "\n";
            }
            break;

        case 2:
            try {
                $symbol = strtoupper(trim(readline("Enter the symbol: ")));
                $currencyInfo = $currencyRepository->search($symbol);
                echo "Currency Name: " . $currencyInfo->getName() . "\n";
                echo "Currency Symbol: " . $currencyInfo->getSymbol() . "\n";
                echo "Current Price (USD): " . number_format($currencyInfo->getPrice(), 8) . "\n";
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage() . "\n";
            }
            break;

        case 3:
            try {
                $symbol = strtoupper(trim(readline("Enter the symbol to buy: ")));
                $amount = floatval(trim(readline("Enter the amount to buy: ")));
                $balance = $walletService->getBalance();
                $cryptoData = $currencyRepository->search($symbol);
                $price = $cryptoData->getPrice();
                $totalCost = $price * $amount;

                if ($balance >= $totalCost) {
                    $buyService->execute($symbol, $amount);
                } else {
                    echo "\033[31mInsufficient balance. Please try again with a lower amount.\033[0m\n";
                }
            } catch (Exception $e) {
                throw new TransactionFailedException("An error occurred: " . $e->getMessage() . "\n");
            }
            break;

        case 4:
            try {
                $symbol = strtoupper(trim(readline("Enter the symbol to sell: ")));
                $amount = floatval(trim(readline("Enter the amount to sell: ")));
                $sellService->execute($symbol, $amount);
            } catch (Exception $e) {
                throw new TransactionFailedException("An error occurred: " . $e->getMessage() . "\n");
            }
            break;

        case 5:
            try {
                $output = new ConsoleOutput();
                $table = new Table($output);
                $table->setHeaders(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp']);

                $transactions = $database->getTransactionsByUserId($userId);
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
            } catch (Exception $e) {
                throw new TransactionGetException("An error occurred: " . $e->getMessage() . "\n");
            }
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
}