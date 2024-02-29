# SportsOD - sports on demand

* This is a simple REST API to book venues, fields or courts for sport events

## Requirements

You must install the following in order to run this project, go to each website and follow the installation instructions according to your operating system

- [PHP](https://www.php.net) 8.2+ installed
- [composer](https://getcomposer.org) installed
- [PostgreSQL](https://www.postgresql.org) installed, also make sure you have the proper driver for php installed
- [REDIS](https://redis.io) installed

## Running the project

To run this project:
- run `composer install`
- in your .env file, set the DB_PASSWORD with your own PostgreSQL password, the DB_USERNAME is set to postgres, but you're free to change it to your own PostgreSQL user
- create a postgres database called sportsod
- run the migrations and seeders with `php artisan migrate:fresh --seed`
- run the REDIS server with `redis-server`
- start the project locally with `php artisan serve`

## Testing

To run the tests, execute:

`php artisan test --testsuite=Feature --stop-on-failure`