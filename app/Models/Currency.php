<?php

namespace CryptoApp\Models;

class Currency
{
    private string $name;
    private string $symbol;
    private float $price;
    private ?int $rank;

    public function __construct(
        string $name,
        string $symbol,
        float $price,
        ?int $rank = null
    )
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
        $this->rank = $rank;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }
}