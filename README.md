# Wireless Logic Tech Test by Pete Siviter, 11 February 2023

This is the Wireless Logic technical test.  It is implemented as a Symfony 6 Cli command which 
can either be run inside the a docker container or stand alone from a bash prompt with the 
correct components already installed.  There is also a docker-compose file that can bring up an environment 
in docker suitable to run this project if needed.

## Running from a bash prompt

cd into the project root and run composer install.

```bash
composer install
```

## Usage
To run the scrape, issue the command
```bash
bin/console wl:test
```

There is one option, to specify an alternative url to fetch the page from, but if
omitted, the default url (https://wltest.dns-systems.net/) is used.  E.g.

```bash
bin/console wl:test https://alternative.url/here
```

## Testing
There are unit tests available which can be run using `bin/phpunit` from the project root.

```bash
bin/phpunit
```

Output should look like
```bash
bin/phpunit
```

## Installation in docker-compose
This project was produced on the [bitnami/symfony](https://hub.docker.com/r/bitnami/symfony/)
Docker Hub project, which was used as a basis for the development work.  It has just two
containers, a Symfony 6 container and an instance of MariaDB which is not used in this project.
To use this environment, issue `docker-compose up` in a directory with the docker-compose.yml file in it. It will
build the environment which will check out a fresh copy of Symfony 6 into a `wl` directory in the same directory.
Replace that project directory with the contents of this repo and run composer install from a bash prompt inside the
container (`docker-compose exec wltechtest bash`). You should then be able to run `bin/console wl:test` from that
bash prompt inside the container.

## Files

Classes

`src/Scraper/WebScraper.php` The scraper itself.

`src/Scraper/ScraperInterface.php` Interface for the above, used to provide ability to substitute a mock in tests.

`src/Factory/WebScraperFactory.php` Simple factory class to generate scrapers.

`src/Factory/WebScraperFactoryInterface.php` interface for the above.

`src/Command/WlTestCommand.php` Symfony Command class that is invoked by the command line above.

Tests

`tests/Scraper/WebScraperTest.php` Unit test of the `WebScraper` class.  Unit test, responses from the http client 
are mocked.

Fixtures

`tests/fixtures/response.html` sample response used in mock http client.


