# iService

RESTful API to do some basic CRUD of vehicles using Test-driven development (TDD).

## Requirements

1. PHP 8.1
2. PostgreSQL or MySQL
3. GIT
4. [(Only Step 1) Install Symfony CLI](https://symfony.com/download#step-1-install-symfony-cli)
5. Make sure all dependencies are installed with the following command:

```bash
symfony check:requirements
```

## Installation

```bash
git clone git@github.com:javier870/iservice.git
cd iservice
```
Open the .env, look for the 3 lines shown below, chose a DBMS, comment out the other one, put your DB access credentials (admin) and pick a DB name for this project that doesn't exist. Then select between "used" or "new" as the VEHICLE_TYPE variable (which defines what type of vehicles will be shown in the app).

DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"
DATABASE_URL="postgresql://symfony:ChangeMe@127.0.0.1:5432/app?serverVersion=13&charset=utf8"

VEHICLE_TYPE=new

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
symfony server:start
```

## TDD

Open the .env.test file and look for the same 3 lines as before to set up the test environment. In this case, Symfony will add a _test suffix to the DB name when creating it. Ex: "iservice_auto" = "iservice_auto_test". This way you can name both DBs, regular and test, with the same name and use the same DBMS with no problem.
Open another terminal, from the project folder, so the server service keeps running, and run the following command to execute all application tests.

```bash
cd <path to app folder>
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:migrations:migrate
php bin/phpunit
```
If a "7/7 (100%)" result is shown, all 7 test methods with 37 assertions ran successfully and the API is ready to be used.

## Usage
To switch between vehicles type "new" and "used" just Edit VEHICLE_TYPE in .env

Open http://127.0.0.1:8000 to see all the requests parameters and responses. It is possible to try the API from this web UI but just for the GETs and DELETE methods, POST and PATCH don't work this way.

In order to try all methods, it's necessary to use any 3rd party application like "Postman".