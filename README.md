# ![ulogger_logo_small](https://cloud.githubusercontent.com/assets/3366666/24080878/0288f046-0ca8-11e7-9ffd-753e5c417756.png)μlogger   [![Build Status](https://travis-ci.org/bfabiszewski/ulogger-server.svg?branch=master)](https://travis-ci.org/bfabiszewski/ulogger-server) [![Coverity Status](https://scan.coverity.com/projects/13688/badge.svg)](https://scan.coverity.com/projects/bfabiszewski-ulogger-server)

This is a web application for real-time collection of geolocation data, tracks viewing and management.
Together with a dedicated [μlogger mobile client](https://github.com/bfabiszewski/ulogger-android) it may be used as a complete self hosted server–client solution for logging and monitoring users' geolocation.

## Live demo:
- http://ulogger.fabiszewski.net/ (test track upload and editing, login: demo, password: demo)

## Requirements:
- PHP 5.5 (5.4 with [password_compat](https://github.com/bfabiszewski/ulogger-server/blob/04b2b771398d8511bfa6fe8a85d58162bd32fc46/helpers/user.php#L24))
- MySQL 4.1
- browser with javascript enabled, cookies for authentication and saving preferences

## Features:
- simple
- allows live tracking
- track statistics
- altitudes graph
- multiple users
- user authentication
- Google Maps API v3
- OpenLayers v2 or v3 (OpenStreet and other layers)
- ajax
- user preferences stored in cookies
- simple admin menu
- export tracks to gpx and kml
- import tracks from gpx

## Install
- Download zipped archive or clone the repository on your computer
- Move it to your web server directory (unzip if needed)
- Create database and MySQL user (at least SELECT, INSERT, UPDATE, DELETE privileges, CREATE, DROP for setup script)
- Create a copy of `config.default.php` and rename it to `config.php`. Customize it and add database credentials
- Edit `scripts/setup.php` script, enable it by setting [$enabled](https://github.com/bfabiszewski/ulogger-server/blob/master/scripts/setup.php#L21) value to `true`
- Make sure you have a web server running with PHP and MySQL
- Open http://YOUR_HOST/ulogger-server/scripts/setup.php page in your browser
- Follow instructions in setup script. It will add database tables and set up your μlogger user
- **Remember to remove or disable `scripts/setup.php` script**
- Log in with your new user on http://YOUR_HOST/ulogger-server/
- You may also want to set your new user as an [admin in config file](https://github.com/bfabiszewski/ulogger-server/blob/v0.2/config.default.php#L67).
- Folders `.docker/` and `.tests/` as well as composer files are needed only for development. May be safely removed.

## Docker
- Run `docker run --name ulogger -p 8080:80 -d bfabiszewski/ulogger` and access `http://localhost:8080` in your browser. Log in with `admin`:`admin` credentials and change default password.
- Optional configuration options with ENV variables, for list see [Dockerfile](https://github.com/bfabiszewski/ulogger-server/blob/master/Dockerfile). The variables correspond to main μlogger configuration parameteres.
- For example: `docker run --name ulogger -e ULOGGER_LANG="pl" -p 8080:80 -d bfabiszewski/ulogger`.
- You may also build the image yourself. Run `docker build .` from the root folder where `Dockerfile` reside. There are optional build-time arguments that allow you to set default database passwords for root and ulogger users.
- For example: `docker build --build-arg DB_ROOT_PASS=secret1 --build-arg DB_USER_PASS=secret2 .`.

## Tests
- Install tests dependecies.
  - `composer install`
- Integration tests may be run against docker image. We need exposed http and mysql ports (eg. mapped to localhost 8080 and 8081).
  - `docker build -t ulogger .`
  - `docker run -d --name ulogger -p 8080:80 -p 8081:3306 --expose 3306 ulogger`
- Use environment variables (or create `.env` file in `.tests/` folder) to set up connection details (below database credentials are docker defaults)
  - `DB_HOST=127.0.0.1`
  - `DB_NAME=ulogger`
  - `DB_USER=ulogger`
  - `DB_PASS=secret2`
  - `DB_PORT=8081`
  - `ULOGGER_URL="http://127.0.0.1:8080"`
- Run tests
  - `./vendor/bin/phpunit -c .tests/phpunit.xml`

## Todo
- improve track editing
- track display filters (accurracy, provider)
- improve interface on mobile devices

## License
- GPL
- μlogger is a fork of phpTrackme - tracks viewer I wrote for TrackMe app
- most icons come from [iconmonstr](https://iconmonstr.com)
