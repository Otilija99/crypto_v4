<?php

namespace CryptoApp\Repositories\Currency;

use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Models\Currency;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

class CoinPaprikaApiCurrencyRepository implements CurrencyRepository
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coinpaprika.com/v1/',
            'timeout' => 2.0,
        ]);
    }

    public function getTop(int $limit = 10): array
    {
        try {
            $response = $this->client->request('GET', 'coins');

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinPaprika. Status Code: ' . $response->getStatusCode());
            }
            $topCoins = array_slice($data, 0, $limit);
            $result = [];

            foreach ($topCoins as $coin) {
                $coinDetailsResponse = $this->client->request('GET', 'tickers/' . $coin['id']);
                $coinDetails = json_decode($coinDetailsResponse->getBody(), true);

                $currency = new Currency(
                    $coin['name'],
                    $coin['symbol'],
                    $coinDetails['quotes']['USD']['price'],
                    $coinDetails['rank'],
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
            $response = $this->client->request('GET', 'coins');
            $coinsList = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinPaprika. Status Code: ' . $response->getStatusCode());
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

            $response = $this->client->request('GET', 'tickers/' . $coinId);
            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                return new Currency (
                    $data['name'],
                    $data['symbol'],
                    $data['quotes']['USD']['price'],
                    $data['rank'],
                );
            } else {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinPaprika. Status Code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException $e) {
            throw new HttpFailedRequestException(
                'Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}