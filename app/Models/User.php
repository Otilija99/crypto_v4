<?php

namespace CryptoApp\Models;

use Ramsey\Uuid\Uuid;

class User
{
    private string $id;
    private string $username;
    private string $password;

    public function __construct(
        string $username,
        string $password,
        string $id = null
    )
    {
        $this->id = $id ?? Uuid::uuid4()->toString();;
        $this->username = $username;
        $this->password = $password;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}