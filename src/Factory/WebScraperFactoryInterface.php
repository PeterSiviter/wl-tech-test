<?php

declare(strict_types=1);

namespace App\Factory;

use App\Scraper\WebScraper;

interface WebScraperFactoryInterface
{
    public function create(): WebScraper;
}