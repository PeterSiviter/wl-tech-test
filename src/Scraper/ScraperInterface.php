<?php

declare(strict_types=1);

namespace App\Scraper;

use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

interface ScraperInterface
{
    public function scrape(string $url, ClientInterface $client): array;
}