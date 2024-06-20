<?php
namespace CryptoApp\Repositories\Currency;

use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Models\Currency;

interface CurrencyRepository
{
    public function getTop(int $limit = 10): array;

    /**
     * @throws HttpFailedRequestException
     */
    public function search(string $symbol): Currency;
}