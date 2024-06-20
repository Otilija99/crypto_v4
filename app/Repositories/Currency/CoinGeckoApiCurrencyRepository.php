<?php

namespace CryptoApp\Repositories\Currency;

use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Exceptions\NoSuchCurrencyException;
use CryptoApp\Models\Currency;
use CryptoApp\Repositories\CurrencyRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinGeckoApiCurrencyRepository implements CurrencyRepository
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coingecko.com/api/v3/',
            'timeout' => 2.0,
        ]);
    }

    public function getTop(int $limit = 10): array
    {
        try {
            $response = $this->client->request('GET', 'coins/markets', [
                'query' => [
                    'vs_currency' => 'usd',
                    'per_page' => $limit,
                    'page' => 1,
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinGecko. Status Code: ' . $response->getStatusCode());
            }
            $result = [];

            foreach ($data as $coin) {
                $currency = new Currency(
                    $coin['name'],
                    $coin['symbol'],
                    $coin['current_price'],
                    $coin['market_cap_rank'] ?? null,
                    );
                $result[] = $currency;
            }

            return $result;

        } catch (GuzzleException $e) {
            throw new HttpFailedRequestException(
                'Failed to make HTTP request: ' . $e->getMessage());
        }
    }

    public function search(string $symbol): Currency
    {
        try {
            $response = $this->client->request('GET', 'coins/list', [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]);

            $coinsList = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                throw new HttpFailedRequestException(
                    'Failed to get currency list from CoinGecko. Status Code: ' . $response->getStatusCode());
            }
            $coinId = null;

            foreach ($coinsList as $coin) {
                if (strtolower($coin['symbol']) === strtolower($symbol)) {
                    $coinId = $coin['id'];
                    break;
                }
            }

            if ($coinId === null) {
                throw new NoSuchCurrencyException('Coin with symbol ' . $symbol . ' not found.');
            }

            $coinResponse = $this->client->request('GET', 'coins/' . $coinId, [
                'query' => [
                    'localization' => 'false',
                ],
            ]);

            $coinData = json_decode($coinResponse->getBody(), true);


            if ($coinResponse->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinGecko. Status Code: ' . $coinResponse->getStatusCode());
            }
            $coinInfo = $coinData['market_data'];

            return new Currency(
                $coinData['name'],
                $coinData['symbol'],
                $coinInfo['current_price']['usd'],
                $$coinData['market_cap_rank'] ?? null
            );

        } catch (GuzzleException $e) {
            throw new HttpFailedRequestException(
                'Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}