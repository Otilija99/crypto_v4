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
        $user = new User(uniqid(), $username, md5($password));
        $this->userRepository->saveUser($user);
    }

    public function login(string $username, string $password): User
    {
        $userData = $this->userRepository->getUserByUsernameAndPassword($username, $password);
        if ($userData === null) {
            throw new UserNotFoundException("Invalid username or password.");
        }
        return new User($userData['id'], $userData['username'], $userData['password']);
    }
}

