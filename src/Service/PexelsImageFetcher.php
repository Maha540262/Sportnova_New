<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PexelsImageFetcher
{
    private string $apiKey = 'nCO38knZJZMrw0PLFgjYMUt89Uz1Jn7LpYdzLD6xwolbmbWQAJh2vufy';

    public function __construct(private HttpClientInterface $client) {}

    public function fetchImage(string $query): ?string
    {
        $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=1';

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->apiKey,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = $response->toArray();
        if (!empty($data['photos'][0]['src']['medium'])) {
            return $data['photos'][0]['src']['medium'];
        }

        return null;
    }
}
