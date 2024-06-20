<?php

namespace CryptoApp\Services;

use CryptoApp\Exceptions\UserLoginException;
use CryptoApp\Models\User;
use CryptoApp\Repositories\User\UserRepository;
use Exception;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(string $username, string $password): void
    {
        $hashedPassword = md5($password);
        $newUser = new User($username, $hashedPassword);
        $this->userRepository->save($newUser);
        echo "User registered successfully with ID: " . $newUser->getId() . ".\n";
    }

    public function login(string $username, string $password): ?User
    {
        try {
            $hashedPassword = md5($password);
            $userData = $this->userRepository->findByUsernameAndPassword($username, $hashedPassword);

            if (!$userData) {
                throw new UserLoginException("Invalid username or password.");
            }

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
