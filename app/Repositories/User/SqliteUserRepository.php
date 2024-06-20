<?php

namespace CryptoApp\Repositories\User;

use CryptoApp\Exceptions\UserNotFoundException;
use CryptoApp\Exceptions\UserSaveException;
use CryptoApp\Models\User;
use Medoo\Medoo;
use Exception;

class SqliteUserRepository implements UserRepository
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
            $this->database->exec('CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(225) PRIMARY KEY,
                username VARCHAR(225) NOT NULL,
                password VARCHAR(225) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )');
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function saveUser(User $user): void
    {
        try {
            $this->database->insert('users', [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
            ]);
        } catch (Exception $e) {
            throw new UserSaveException("Failed to save user: " . $e->getMessage());
        }
    }

    public function getUserById(string $userId): ?array
    {
        try {
            $userData = $this->database->get('users', '*', ['id' => $userId]);
            return $userData ?: null;
        } catch (Exception $e) {
            throw new UserNotFoundException("User not found: " . $e->getMessage());
        }
    }

    public function getUserByUsernameAndPassword(string $username, string $password): ?array
    {
        $hashedPassword = md5($password);

        try {
            $userData = $this->database->get('users', '*', [
                'username' => $username,
                'password' => $hashedPassword,
            ]);

            if (!$userData) {
                throw new UserNotFoundException("User doesn't exist or incorrect password!");
            }
            return $userData;
        } catch (Exception $e) {
            throw new UserNotFoundException("User doesn't exist or incorrect password!");
        }
    }
}
