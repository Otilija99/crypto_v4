<?php
namespace CryptoApp\Services;

use CryptoApp\Exceptions\UserLoginException;
use CryptoApp\Models\User;
use CryptoApp\Database\DatabaseInterface;
use Exception;

class UserService
{
    private DatabaseInterface $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function createUser(string $username, string $password): void
    {
        $hashedPassword = md5($password);
        $newUser = new User($username, $hashedPassword);
        $this->database->saveUser($newUser);
        echo "User registered successfully with ID: " . $newUser->getId() . ".\n";
    }

    public function login(string $username, string $password): ?User
    {
        try {
            $userData = $this->database->getUserByUsernameAndPassword($username, $password);

            return new User(
                $userData['username'],
                $userData['password'],
                $userData['id']

            );
        } catch (Exception $e) {
            throw new UserLoginException("Login failed: " . $e->getMessage());
        }
    }
}