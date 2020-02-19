# ![ulogger_logo_small](https://cloud.githubusercontent.com/assets/3366666/24080878/0288f046-0ca8-11e7-9ffd-753e5c417756.png)μlogger   [![Build Status](https://travis-ci.com/bfabiszewski/ulogger-server.svg?branch=master)](https://travis-ci.com/bfabiszewski/ulogger-server)

This is a web application for real-time collection of geolocation data, tracks viewing and management.
Together with a dedicated [μlogger mobile client](https://github.com/bfabiszewski/ulogger-android) it may be used as a complete self hosted server–client solution for logging and monitoring users' geolocation.

## Live demo:
- http://ulogger-demo.herokuapp.com (test track upload with Android app and editing, login: demo, password: demo)

## Minimum requirements:
- PHP 5.5
- PHP extensions: ctype, json, pdo (with respective drivers), session, simplexml, xmlwriter, xdebug (only for tests)
- MySQL, PostgreSQL or SQLite (over PDO driver)
- browser with javascript enabled, cookies for authentication and saving preferences

## Features:
- simple
- allows live tracking
- track statistics
- altitudes graph
- multiple users
- user authentication
- Google Maps
- OpenLayers (OpenStreet and other layers)
- user preferences stored in cookies
- simple admin menu
- export tracks to gpx and kml
- import tracks from gpx

## Install
- Download zipped archive or clone the repository on your computer
- Move it to your web server directory (unzip if needed)
- Fix folder permissions: `uploads` folder (for uploaded images) should be writeable by PHP scripts
- In case of development version it is necessary to build javascript bundle from source files. You will need to install `npm` and run `npm install` and `npm run build` in root folder
- Create database and database user (at least SELECT, INSERT, UPDATE, DELETE privileges, CREATE, DROP for setup script, SEQUENCES for postgreSQL)
- Create a copy of `config.default.php` and rename it to `config.php`. Customize it and add database credentials
- Edit `scripts/setup.php` script, enable it by setting [$enabled](https://github.com/bfabiszewski/ulogger-server/blob/master/scripts/setup.php#L21) value to `true`
- Make sure you have a web server running with PHP and chosen database
- Open http://YOUR_HOST/ulogger-server/scripts/setup.php page in your browser
- Follow instructions in setup script. It will add database tables and set up your μlogger user
- **Remember to remove or disable `scripts/setup.php` script**
- Log in with your new user on http://YOUR_HOST/ulogger-server/
- You may also want to set your new user as an [admin in config file](https://github.com/bfabiszewski/ulogger-server/blob/v0.2/config.default.php#L67).
- Folders `.docker/` and `.tests/` as well as composer files are needed only for development. May be safely removed.

## Upgrade to version 1.x
- TODO: convert following notes to migration script
- Database changes:
  - `ALTER TABLE positions CHANGE image_id image VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL`
  - `ALTER TABLE users ADD admin BOOLEAN NOT NULL DEFAULT FALSE AFTER password`
  - new tables for config values: `config` and `ol_layers`, see SQL files in scripts folder, eg. [mysql](https://github.com/bfabiszewski/ulogger-server/blob/master/scripts/ulogger.mysql)
  - modify admin user entry in `users` table: set `admin` to `true`
- Config file changes: only database setup is defined in config file, see [config.default.php](https://github.com/bfabiszewski/ulogger-server/blob/master/config.default.php) for valid values

## Docker
- Run `docker run --name ulogger -p 8080:80 -d bfabiszewski/ulogger` and access `http://localhost:8080` in your browser. Log in with `admin`:`admin` credentials and change default password.
- Optional configuration options with ENV variables, for list see [Dockerfile](https://github.com/bfabiszewski/ulogger-server/blob/master/Dockerfile). The variables correspond to main μlogger configuration parameteres.
- For example: `docker run --name ulogger -e ULOGGER_LANG="pl" -p 8080:80 -d bfabiszewski/ulogger`.
- You may also build the image yourself. Run `docker build .` from the root folder where `Dockerfile` reside. There are optional build-time arguments that allow you to set default database passwords for root and ulogger users.
- For example: `docker build --build-arg DB_ROOT_PASS=secret1 --build-arg DB_USER_PASS=secret2 --build-arg DB_DRIVER=sqlite .`.

## Tests
- Install tests dependecies.
  - `composer install`
  - `npm install`
- Integration tests may be run against docker image. We need exposed http and optionally database ports (eg. mapped to localhost 8080 and 8081). Below example for MySQL setup.
  - `docker build -t ulogger .`
  - `docker run -d --name ulogger -p 8080:80 -p 8081:3306 --expose 3306 -e ULOGGER_ENABLE_SETUP=1 ulogger`
- Use environment variables (or create `.env` file in `.tests/` folder) to set up connection details (below database credentials are docker defaults)
  - `DB_DSN="mysql:host=127.0.0.1;port=8081;dbname=ulogger;charset=utf8"`
  - `DB_USER=ulogger`
  - `DB_PASS=secret2`
  - `ULOGGER_URL="http://127.0.0.1:8080"`
- PHP tests
  - `./vendor/bin/phpunit -c .tests/phpunit.xml`
- JS tests
  - `npm test`  
- Other tests
  - `npm run lint:js`
  - `npm run lint:css`

## Translations
- translations may be contributed via [Transifex](https://www.transifex.com/bfabiszewski/ulogger/).

## Donate
[![Donate paypal](https://img.shields.io/badge/donate-paypal-green.svg)](https://www.paypal.me/bfabiszewski)  
![Donate bitcoin](https://img.shields.io/badge/donate-bitcoin-green.svg) `bc1qt3uwhze9x8tj6v73c587gprhufg9uur0rzxhvh`  
![Donate ethereum](https://img.shields.io/badge/donate-ethereum-green.svg) `0x100C31C781C8124661413ed6d1AA9B1e2328fFA2`  

## License
- GPL
- most icons come from [iconmonstr](https://iconmonstr.com)
