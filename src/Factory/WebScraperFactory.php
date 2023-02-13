<?php

declare(strict_types=1);

namespace App\Factory;

use App\Scraper\WebScraper;

final class WebScraperFactory implements WebScraperFactoryInterface
{
    public function create(): WebScraper
    {
        return new WebScraper();
    }
}