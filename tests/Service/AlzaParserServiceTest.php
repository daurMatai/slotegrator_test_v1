<?php

namespace App\Tests\Service;

use App\Service\AlzaParserService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AlzaParserServiceTest extends TestCase
{
    public function testParseProduct(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockHttpClient->method('request')
            ->with('GET', 'http://example.com/product')
            ->willReturn($mockResponse);

        $mockResponse->method('getContent')
            ->willReturn('<html><div id="title">Example Product</div><div id="price">99.99</div><img id="image" src="http://example.com/image.jpg"></html>');

        $parser = new AlzaParserService($mockHttpClient);

        $result = $parser->parseProduct('http://example.com/product');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('price', $result);
        $this->assertArrayHasKey('photo', $result);
        $this->assertEquals('Example Product', $result['name']);
        $this->assertEquals('99.99', $result['price']);
        $this->assertEquals('http://example.com/image.jpg', $result['photo']);
    }

    public function testParseProductThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('title not found');

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockHttpClient->method('request')
            ->with('GET', 'http://example.com/product')
            ->willReturn($mockResponse);

        $mockResponse->method('getContent')
            ->willReturn('<html></html>');

        $parser = new AlzaParserService($mockHttpClient);

        $parser->parseProduct('http://example.com/product');
    }
}