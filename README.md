# dbwebb-mvc

![Leaf Image](https://dbwebb.se/image/theme/leaf_256x256.png)

## Overview

This repository is part of the course [dbwebb/mvc-2](https://dbwebb.se/kurser/mvc-v2) offered at [BTH (Blekinge Institute of Technology)](https://www.bth.se). It contains a Symfony 7.2 web application following the MVC architecture.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Clone the Repository](#clone-the-repository)
- [Install Dependencies](#install-dependencies)
- [Build Frontend Assets](#build-frontend-assets)
- [Run the Application](#run-the-application)

## Prerequisites

Before running the app, make sure you have the following installed:

- PHP (version 7.4 or higher)
- Composer (dependency management)
- Symfony 7.2
- Node.js and npm (for Encore)
- Symfony Encore (Webpack Encore)
- A web server like Apache or Nginx (optional, if not using Symfony's built-in server)
- A database server (e.g., MySQL, PostgreSQL) for app data

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

## Run the Application

To start the Symfony application, you can use Symfony’s built-in web server. Run the following command:

```bash
symfony serve
```

Alternatively, you can use your own web server (e.g., Apache, Nginx) if you prefer. Ensure the document root points to the `public` folder of the Symfony app.

Once the server is running, open your browser and go to:

```
http://localhost:8000
```

You should now see the Symfony app in action!
