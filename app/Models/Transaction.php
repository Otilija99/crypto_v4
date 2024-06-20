<?php

namespace CryptoApp\Models;

use JsonSerializable;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Transaction implements JsonSerializable
{
    const TYPE_BUY = 'buy';
    const TYPE_SELL = 'sell';

    private string $id;
    private string $userId;
    private string $type;
    private string $symbol;
    private float $amount;
    private float $price;
    private Carbon $timestamp;


    public function __construct(
        string $userId,
        string $type,
        string $symbol,
        float $amount,
        float $price,
        Carbon $timestamp

    )
    {
        $this->id = Uuid::uuid4()->toString();;
        $this->userId = $userId;
        $this->setType($type);
        $this->setSymbol($symbol);
        $this->amount = $amount;
        $this->price = $price;
        $this->timestamp = $timestamp;

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    private function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_BUY, self::TYPE_SELL])) {
            throw new InvalidArgumentException('Invalid transaction type');
        }
        $this->type = $type;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    private function setSymbol(string $symbol): void
    {
        if (preg_match('/^[A-Z0-9]+$/', $symbol) !== 1) {
            throw new InvalidArgumentException('Invalid symbol format');
        }
        $this->symbol = strtoupper($symbol);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTimestamp(): Carbon
    {
        return $this->timestamp;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'type' => $this->type,
            'symbol' => $this->symbol,
            'amount' => $this->amount,
            'price' => $this->price,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['user_id'],
            $data['type'],
            $data['symbol'],
            $data['amount'],
            $data['price'],
            Carbon::createFromFormat('Y-m-d H:i:s', $data['timestamp']),
        );
    }
}