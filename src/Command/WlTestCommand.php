<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\WebScraperFactory;
use App\Factory\WebScraperFactoryInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Teapot\HttpException;
use Teapot\StatusCode;

#[AsCommand(
    name: 'wl:test',
    description: 'Return a JSON array of all products at the URL https://wltest.dns-systems.net2',
)]
final class WlTestCommand extends Command implements StatusCode
{
    private const DEFAULT_WEB_PAGE = 'https://wltest.dns-systems.net';

    private WebScraperFactoryInterface $scraperFactory;

    protected function configure(): void
    {
        $this
            ->addArgument(
                'page',
                InputArgument::OPTIONAL,
                sprintf('The web page to scrape (default: %s)', self::DEFAULT_WEB_PAGE),
                self::DEFAULT_WEB_PAGE
            );

        $this->scraperFactory = new WebScraperFactory();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $webPage = $input->getArgument('page');

        if ($webPage) {
            $io->note(sprintf('Attempting to scrape: %s...', $webPage));

            try {
                // Create our scraper
                $scraper = $this->scraperFactory->create();

                // Create new web client to provide to the scraper
                $client = new Client();

                // Scrape to get an array of elements, any exceptions thrown will be caught below
                $items = $scraper->scrape($webPage, $client);
            } catch (HttpException $e) {
                // Logging the exception type helps identify what went wrong, hence different exception handlers here
                $io->writeln('HttpException: ' . $e->getMessage() . " " . $e->getCode());
                return Command::FAILURE;
            } catch (GuzzleException $e) {
                $io->writeln('GuzzleException: ' . $e->getMessage() . " " . $e->getCode());
                return Command::FAILURE;
            } catch (Exception $e) {
                // Catch the rest
                $io->writeln('Exception: ' . $e->getMessage() . " " . $e->getCode());
                return Command::FAILURE;
            }

            // Output the result in json
            $io->writeln(json_encode($items, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $io->writeln('Should never get here (either a parameter is provided or the default is used).');
        return Command::FAILURE;
    }
}
