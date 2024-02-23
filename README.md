# SportsOD - sports on demand

* This is a simple REST API to book venues, fields or courts for sport events

## Requirements

You must install the following in order to run this project, go to each website and follow the installation instructions according to your operating system

- [PHP](https://www.php.net) 8.2+ installed
- [composer](https://getcomposer.org) installed
- [PostgreSQL](https://www.postgresql.org) installed, also make sure you have the proper driver for php installed
- [REDIS](https://redis.io) installed

To run this project:
- rename .env.example for .env
- in your .env file, set the DB_PASSWORD with your own PostgreSQL password, the DB_USERNAME is set to postgres, but you're free to change it for your own PostgreSQL user
- run `composer install`
- create a postgres database called sportsod
- run the migrations and seeders with `php artisan migrate:fresh --seed`
- start the project locally with `php artisan serve`