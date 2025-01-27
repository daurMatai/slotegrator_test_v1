<?php

namespace App\Service;

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AlzaParserService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function parseProduct(string $url): array
    {
        $response = $this->httpClient->request('GET', $url);

        $html = $response->getContent();

        $crawler = new Crawler($html);

        if ($crawler->filter('#title')->count() === 0) {
            throw new Exception('title not found');
        } else {
            $name = $crawler->filter('#title')->text();
        }

        if ($crawler->filter('#price')->count() === 0) {
            throw new Exception('price not found');
        } else {
            $price = $crawler->filter('#price')->text();
        }

        if ($crawler->filter('#image')->count() === 0) {
            throw new Exception('image not found');
        } else {
            $photo = $crawler->filter('#image')->attr('src');
        }

        return [
            'name' => trim($name),
            'price' => (double) trim($price),
            'photo' => trim($photo),
        ];
    }
}