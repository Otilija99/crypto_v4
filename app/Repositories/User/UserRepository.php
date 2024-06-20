<?php

namespace CryptoApp\Repositories\User;

use CryptoApp\Models\User;

interface UserRepository
{
    public function saveUser(User $user): void;
    public function getUserById(string $userId): ?array;
    public function getUserByUsernameAndPassword(string $username, string $password): ?array;
}
