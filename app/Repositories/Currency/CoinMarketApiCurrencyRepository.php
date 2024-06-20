<?php

namespace CryptoApp\Repositories\Currency;

use CryptoApp\Exceptions\HttpFailedRequestException;
use CryptoApp\Models\Currency;
use CryptoApp\Repositories\CurrencyRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinMarketApiCurrencyRepository implements CurrencyRepository
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'timeout' => 2.0,
        ]);
        $this->apiKey = $apiKey;
    }

    public function getTop(int $limit = 10): array
    {
        try {
            $response = $this->client->request('GET', 'cryptocurrency/listings/latest', [
                'query' => [
                    'start' => '1',
                    'limit' => $limit,
                    'convert' => 'USD'
                ],
                'headers' => [
                    'X-CMC_PRO_API_KEY' => $this->apiKey,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinMarketCap. Status Code: ' . $response->getStatusCode());
            }
            $topCoins = $data['data'];
            $result = [];

            foreach ($topCoins as $coin) {
                $coinDetailsResponse = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
                    'query' => [
                        'symbol' => $coin['symbol'],
                        'convert' => 'USD'
                    ],
                    'headers' => [
                        'X-CMC_PRO_API_KEY' => $this->apiKey,
                    ],
                ]);

                $coinDetails = json_decode($coinDetailsResponse->getBody(), true);
                $coinDetail = $coinDetails['data'][$coin['symbol']];

                $currency = new Currency(
                    $coin['name'],
                    $coin['symbol'],
                    $coinDetail['quote']['USD']['price'],
                    $coin['cmc_rank'],
                    );
                $result[] = $currency;
            }

            return $result;

        } catch (GuzzleException $e) {
            throw new HttpFailedRequestException('Failed to make HTTP request: ' . $e->getMessage());
        }
    }

    public function search(string $symbol): Currency
    {
        try {
            $response = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
                'query' => [
                    'symbol' => $symbol,
                    'convert' => 'USD'
                ],
                'headers' => [
                    'X-CMC_PRO_API_KEY' => $this->apiKey,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to get data from CoinMarketCap. Status Code: ' . $response->getStatusCode());
            }
            $coinDetail = $data['data'][$symbol];
            return new Currency(
                $coinDetail['name'],
                $coinDetail['symbol'],
                $coinDetail['quote']['USD']['price'],
                $coinDetail ['cmc_rank'] ?? null,
                );

        } catch (GuzzleException $e) {
            throw new HttpFailedRequestException(
                'Failed to make HTTP request: ' . $e->getMessage());
        }
    }
}