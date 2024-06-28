<?php

namespace CryptoApp\Services;

use CryptoApp\Repositories\User\UserRepository;
use CryptoApp\Models\User;
use CryptoApp\Exceptions\UserNotFoundException;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(string $username, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user = new User(uniqid(), $username, $hashedPassword);
        $this->userRepository->saveUser($user);
    }

    public function login(string $username, string $password): User
    {
        $userData = $this->userRepository->getUserByUsernameAndPassword($username, $password);
        if ($userData === null) {
            throw new UserNotFoundException("Invalid username or password.");
        }

        // Verify the password
        if (!password_verify($password, $userData['password'])) {
            throw new UserNotFoundException("Invalid username or password.");
        }

        return new User($userData['id'], $userData['username'], $userData['password']);
    }
}
