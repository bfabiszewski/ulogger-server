# ![ulogger_logo_small](https://cloud.githubusercontent.com/assets/3366666/24080878/0288f046-0ca8-11e7-9ffd-753e5c417756.png)μlogger

This is a web application for real-time collection of geolocation data, tracks viewing and management.
Together with a dedicated [μlogger mobile client](https://github.com/bfabiszewski/ulogger-android) it may be used as a complete self hosted server–client solution for logging and monitoring users' geolocation.

## Live demo:
- http://flaa.fabiszewski.net/ulogger/

## Requirements:
- PHP 5.5 (5.4 with [password_compat](https://github.com/bfabiszewski/ulogger-server/blob/master/helpers/user.php#L24))
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

## Install
- Download the zip or clone the repository on your computer
- Move it to your web server directory
- Use script/ulogger.sql file to create database and tables (you can use a MySQL interface such as [PhpMyAdmin](https://www.phpmyadmin.net))
- Create a copy of config.default.php and rename it config.php. Add database credentials in it
- Make sure you have a web server running, e.g. Apache, also PHP and MySQL
- Open a browser and go to http://your_local_server/ulogger-server/
- Connect with admin/admin
- **Change admin password**
- Create other user if needed

## Todo
- install script
- custom icons
- track editing
- track display filters (accurracy, provider)

## License
- GPL
- μlogger is a fork of phpTrackme - tracks viewer I wrote for TrackMe app
- most icons come from [iconmonstr](https://iconmonstr.com)
