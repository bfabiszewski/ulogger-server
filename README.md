# ![ulogger_logo_small](https://cloud.githubusercontent.com/assets/3366666/24080878/0288f046-0ca8-11e7-9ffd-753e5c417756.png)μlogger

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
- OpenLayers v2 (OpenStreet and other layers)
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

## Docker
- Run `docker run --name ulogger -p 8080:80 -d bfabiszewski/ulogger` and access `http://localhost:8080` in your browser.
- Optional configuration options with ENV variables, for list see [Dockerfile](https://github.com/bfabiszewski/ulogger-server/blob/master/Dockerfile).
- For example: `docker run --name ulogger -e ULOGGER_LANG="pl" -p 8080:80 -d bfabiszewski/ulogger`.

## Todo
- improve track editing
- track display filters (accurracy, provider)
- improve interface on mobile devices

## License
- GPL
- μlogger is a fork of phpTrackme - tracks viewer I wrote for TrackMe app
- most icons come from [iconmonstr](https://iconmonstr.com)
