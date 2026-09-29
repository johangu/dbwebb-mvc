# dbwebb-mvc

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/johangu/dbwebb-mvc/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/johangu/dbwebb-mvc/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/johangu/dbwebb-mvc/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/johangu/dbwebb-mvc/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/johangu/dbwebb-mvc/badges/build.png?b=main)](https://scrutinizer-ci.com/g/johangu/dbwebb-mvc/build-status/main)

![Leaf Image](https://dbwebb.se/image/theme/leaf_256x256.png)

## Overview

This repository is part of the course [dbwebb/mvc-2](https://dbwebb.se/kurser/mvc-v2) offered at [BTH (Blekinge Institute of Technology)](https://www.bth.se). It contains a Symfony 7.2 web application following the MVC architecture, with the report site for the course moments kmom01-06 and the final project in kmom10.

## Table of Contents

- [Contents](#contents)
- [Prerequisites](#prerequisites)
- [Clone the Repository](#clone-the-repository)
- [Install Dependencies](#install-dependencies)
- [Build Frontend Assets](#build-frontend-assets)
- [Set Up the Database](#set-up-the-database)
- [Run the Application](#run-the-application)
- [Tests and Code Quality](#tests-and-code-quality)

## Contents

The application is built up over the course moments, and each part lives under its own routes.

| Course moment | Content | Routes |
| --- | --- | --- |
| kmom01 | The report site with a presentation, the course, the reports and a lucky number, plus a first JSON route | `/`, `/about`, `/report`, `/lucky`, `/api`, `/api/quote` |
| kmom02 | Cards and a deck of cards in classes, stored in the session, with a JSON API | `/card`, `/session`, `/api/deck` |
| kmom03 | The card game 21 against the bank | `/game`, `/game/doc`, `/api/game` |
| kmom04 | Unit tests with PHPUnit, code coverage and generated documentation | `docs/coverage`, `docs/api` |
| kmom05 | A library with books stored in SQLite via Doctrine ORM | `/library`, `/api/library/books` |
| kmom06 | Code quality with phpmetrics and Scrutinizer | `/metric`, `docs/metrics` |
| kmom10 | The project Piratäventyret, an adventure game where all content lives in the database, with a JSON API and a highscore list | `/proj`, `/proj/play`, `/proj/highscore`, `/proj/cheat`, `/proj/about`, `/proj/about/database`, `/proj/api` |

## Prerequisites

Before running the app, make sure you have the following installed:

- PHP (version 8.3 or higher) with the `pdo_sqlite` extension
- Composer (dependency management)
- Node.js and npm (for Webpack Encore)
- The Symfony CLI (optional, for `symfony serve`)

No database server is needed, the application uses SQLite and stores its data in `var/data.db`.

## Clone the Repository

To clone this repository to your local machine, use the following command:

```bash
git clone https://github.com/johangu/dbwebb-mvc.git
```

## Install Dependencies

Once you've cloned the repository, navigate to the project directory and install the required PHP and JavaScript dependencies:

```bash
cd dbwebb-mvc
composer install
npm install
```

## Build Frontend Assets

To compile the frontend assets using Symfony Encore, run the following command:

```bash
npm run dev
```

For a production build (minified and optimized), run:

```bash
npm run build
```

Encore will process and output the compiled assets into the `public/build` directory.

## Set Up the Database

Create the SQLite database and its tables by running the migrations:

```bash
php bin/console doctrine:migrations:migrate
```

The content of the database is then loaded from the web application:

- For the library, go to `/library` and click "Reset library".
- For the project, go to `/proj` and click "Återställ databasen", which loads the rooms, items and interactions of the adventure. The highscore list is kept when the database is reset.

## Run the Application

To start the Symfony application, you can use Symfony’s built-in web server. Run the following command:

```bash
symfony serve
```

If you don't have the Symfony CLI installed, PHP's built-in web server works as well:

```bash
php -S localhost:8000 -t public
```

Alternatively, you can use your own web server (e.g., Apache, Nginx) if you prefer. Ensure the document root points to the `public` folder of the Symfony app.

Once the server is running, open your browser and go to:

```
http://localhost:8000
```

You should now see the Symfony app in action!

## Tests and Code Quality

The development tools are installed separately in the `tools/` directory, run `composer install` in each of `tools/php-cs-fixer`, `tools/phpmd`, `tools/phpstan` and `tools/phpmetrics` before using them.

```bash
composer phpunit      # Run the unit tests and generate code coverage to docs/coverage
composer lint         # Fix code style and run phpmd and phpstan
composer phpdoc       # Generate the API documentation to docs/api
composer phpmetrics   # Generate metrics to docs/metrics
```

Code coverage requires Xdebug. Controllers, entities, repositories and forms from the course moments that are not relevant to test are excluded from the coverage report.
