# μlogger

This is a web application for real-time collection of geolocation data, tracks viewing and management. 
Together with a dedicated [μlogger mobile client](https://github.com/bfabiszewski/ulogger-android) it may be used as a complete self hosted server–client solution for logging and monitoring users' geolocation.

## Live demo:
- http://flaa.fabiszewski.net/ulogger/

## Requirements:
- PHP 5.5
- MYSQL 4.1
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
1. Download the zip or clone the repository on your computer
2. Move it to your web server directory
3. Go to http://your_local_server/ulogger-server/
4. Connect with username admin and password admin
5. Change admin password (not available for now)
6. Create other user if needed

## Todo
- install script
- custom icons
- improve admin menu (password or username change)
- track editing
- track display filters (accurracy, provider)

## License
- GPL
- μlogger is a fork of phpTrackme - tracks viewer I wrote for TrackMe app
