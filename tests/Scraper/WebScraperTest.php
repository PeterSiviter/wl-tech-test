<?php

namespace App\Tests\Scraper;
use App\Factory\WebScraperFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

use App\Scraper\WebScraper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Teapot\HttpException;

/**
 * TODO: Many more tests!
 */
class WebScraperTest extends TestCase
{
    private const DEFAULT_WEB_PAGE = 'https://wltest.dns-systems.net';

    public function testWebScraperReturnsExpectedArrayFromParsedBody(): void
    {
        // Create a mock http handler and queue the good response
        $mockHandler = new MockHandler([
            new Response(200, [], $this->getResponseText()),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $webPage = self::DEFAULT_WEB_PAGE;

        $scraperFactory = new WebScraperFactory();
        $scraper = $scraperFactory->create();

        $items = $scraper->scrape($webPage, $client);

        $this->assertSame(6, count($items));
    }

    public function testWebScraperThrowsExceptionWithInvalidUrl(): void
    {
        // Create a mock http handler and queue any old response (not used for this test)
        $mockHandler = new MockHandler([
            new Response(200, [], ""),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $webPage = 'not-a-url';

        $scraperFactory = new WebScraperFactory();
        $scraper = $scraperFactory->create();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(sprintf('Web Scraping Exception: %s is not a valid URL', $webPage));

        $scraper->scrape($webPage, $client);
    }

    private function getResponseText(): string
    {
        return file_get_contents(__DIR__ . '../../fixtures/response.html');
    }

}
