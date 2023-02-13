<?php

declare(strict_types=1);

namespace App\Scraper;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Teapot\HttpException;
use Teapot\StatusCode;

final class WebScraper implements ScraperInterface
{
    private const INVALID_URL = -1;

    private SymfonyStyle $io;

    /**
     * Use Guzzle to fetch the url and then parse it
     * @throws HttpException
     * @throws GuzzleException
     */
    public function scrape(string $url, ClientInterface $client): array
    {
        // TODO: better url validation as filter_var can be flakey
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new HttpException(
                sprintf('Web Scraping Exception: %s is not a valid URL', $url),
                self::INVALID_URL
            );
        }

        $result = $client->request('GET', $url);
        if (StatusCode::OK !== $result->getStatusCode()) {
            throw new HttpException(
                'Web Scraping Exception', // TODO: more detail here
                $result->getStatusCode()
            );
        }

        return $this->parseBody((string) $result->getBody());
    }

    /**
     * Parse the web pages body to extract the relevant information from the returned table
     */
    protected function parseBody(string $body): array
    {
        // Disable error output direct to console from libxml
        libxml_use_internal_errors(true);

        $domDoc = new DOMDocument();
        $domDoc->loadHTML($body);
        $xPath = new DOMXPath($domDoc);

        $results = [];

        // Extract each element in turn
        $expression = '//div[starts-with(@class, "package ")]//h3';
        $this->extract($expression, 'option-title', $xPath, $results);

        $expression = '//div[starts-with(@class, "package-description")]';
        $this->extract($expression, 'description', $xPath, $results);

        $expression = '//div[starts-with(@class, "package-price")]';
        $this->extract($expression, 'package-price', $xPath, $results);

        $expression = '//div[starts-with(@class, "package-data")]';
        $this->extract($expression, 'package-data', $xPath, $results);

        $this->postProcess($results);

        return $results;
    }

    private function extract(string $expression, string $element, DOMXPath $xPath, array &$results): void
    {
        $optionTitles = $xPath->query($expression);

        foreach ($optionTitles as $n => $optionTitle) {
            $results[$n][$element] = $optionTitle->nodeValue;
        }
    }

    /**
     * Handle post processing, cleanup, calc discount and sort the array.
     */
    private function postProcess(array &$results)
    {
        foreach ($results as &$result) {
            $result['discount'] = 0;
            $parts = explode('Save', $result['package-price']);
            if(count($parts) > 1) {
                $result['discount'] = preg_replace('/[^0-9,.]+/', '', $parts[1]);
                $result['package-price'] = $parts[0];
            }

            $result['price'] = $result['package-price'] =
                trim(preg_replace('/[^0-9,.]+/', '', $result['package-price']), '.');
            $result['annual-price'] = strtok($result['package-data'], ' ') == '12' ?
                (float)(12 * trim($result['package-price'], '.')):
                (float)trim($result['package-price'], '.');
            unset($result['package-data']);
            unset($result['package-price']);
        }

        // Sort by annual price descending
        usort($results, function($a, $b) {
            return $b['annual-price'] > $a['annual-price'] ? 1 : -1;
        });
    }
}