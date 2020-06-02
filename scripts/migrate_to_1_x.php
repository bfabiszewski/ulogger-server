<?php
/**
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

/*
 * CLI script for database migration from μlogger version 0.6 to version 1.x.
 * Database user defined in config file must have privileges to create and modify tables.
 *
 * Backup your database before running this script.
 */

if (PHP_SAPI !== "cli") {
  die("This script must be called from CLI console");
}

if (!defined("ROOT_DIR")) { define("ROOT_DIR", dirname(__DIR__)); }

include_once(ROOT_DIR . "/helpers/config.php");
include_once(ROOT_DIR . "/helpers/db.php");

if (!defined("SKIP_RUN")) {
  verifyVersion() || exit(1);
  waitForUser() || exit(0);
  updateSchemas() || exit(1);
  updateConfig() || exit(1);
  exit(0);
}

/**
 * Check μlogger version is valid for migration
 * @return bool True if valid version
 */
function verifyVersion() {
  if (!class_exists("uConfig") || !class_exists("uDb") ||
    !method_exists("uConfig", "getOfflineInstance") ||
    strpos(uConfig::getOfflineInstance()->version, '1.') !== 0) {
    echo "You need μlogger version 1.x to run this script" . PHP_EOL;
    return false;
  }
  return true;
}

/**
 * Waits for user confirmation
 * @param string $input Optional path to read from (for tests)
 * @return bool True if confirmed
 */
function waitForUser($input = "php://stdin") {
  echo "This script will update database from version 0.6 to 1.x." . PHP_EOL;
  echo "Creating database backup is recommended before proceeding" . PHP_EOL;
  echo "Type 'yes' to continue, anything else to abort" . PHP_EOL;
  $handle = fopen($input, 'rb');
  $input = fgets($handle);
  fclose($handle);
  if (trim($input) !== "yes") {
    echo "Cancelled by user" . PHP_EOL;
    return false;
  }
  echo PHP_EOL;
  echo "Starting migration..." . PHP_EOL;
  return true;
}

/**
 * Updates database schemas
 * @return bool True on success
 */
function updateSchemas() {
  echo "Migrating database schemas..." . PHP_EOL;
  try {
    $queries = getQueries();
    uDb::getInstance()->beginTransaction();
    foreach ($queries as $query) {
      uDb::getInstance()->exec($query);
    }
    uDb::getInstance()->commit();
  } catch (PDOException $e) {
    echo "Database query failed: {$e->getMessage()}" . PHP_EOL;
    echo "Reverting changes..." . PHP_EOL;
    uDb::getInstance()->rollBack();
    return false;
  }
  echo PHP_EOL;
  echo "Database schemas updated successfully" . PHP_EOL;
  return true;
}

/**
 * Update database settings based on current config file
 * @param string $path Optional path of config (for tests)
 * @return bool True on success
 */
/** @noinspection IssetArgumentExistenceInspection, PhpIncludeInspection */
function updateConfig($path = ROOT_DIR . "/config.php") {
  echo "Migrating config to database..." . PHP_EOL;
  require_once($path);
  if (isset($admin_user) && !empty($admin_user)) {
    try {
      echo "Setting user $admin_user as admin" . PHP_EOL;
      $query = "UPDATE " . uDb::getInstance()->table('users') . " SET admin = ? WHERE login = ?";
      $stmt = uDb::getInstance()->prepare($query);
      $stmt->execute([ 1, $admin_user ]);
      if ($stmt->rowCount() === 0) {
        echo "User $admin_user not found in database table" . PHP_EOL;
        echo "Set your admin user manually in users table" . PHP_EOL;
      }
    } catch (PDOException $e) {
      echo "Setting admin user failed: " . $e->getMessage() . PHP_EOL;
      echo "Set your admin user manually in users table" . PHP_EOL;
    }
  }
  $config = uConfig::getOfflineInstance();
  if (isset($mapapi) && !empty($mapapi)) {
    $config->mapApi = $mapapi;
  }
  if (isset($ol_layers) && is_array($ol_layers)) {
    $id = 1;
    foreach ($ol_layers as $name => $url) {
      $config->olLayers[] = new uLayer($id++, $name, $url, 0);
    }
  }
  if (isset($init_latitude) && is_numeric($init_latitude)) {
    $config->initLatitude = $init_latitude;
  }
  if (isset($init_longitude) && is_numeric($init_longitude)) {
    $config->initLongitude = $init_longitude;
  }
  if (isset($gkey) && !empty($gkey)) {
    $config->googleKey = $gkey;
  }
  if (isset($require_authentication) && is_numeric($require_authentication)) {
    $config->requireAuthentication = (bool) $require_authentication;
  }
  if (isset($public_tracks) && is_numeric($public_tracks)) {
    $config->publicTracks = (bool) $public_tracks;
  }
  if (isset($pass_lenmin) && is_numeric($pass_lenmin)) {
    $config->passLenMin = (int) $pass_lenmin;
  }
  if (isset($pass_strength) && is_numeric($pass_strength)) {
    $config->passStrength = (int) $pass_strength;
  }
  if (isset($interval) && is_numeric($interval)) {
    $config->interval = (int) $interval;
  }
  if (isset($lang) && !empty($lang)) {
    $config->lang = $lang;
  }
  if (isset($units) && !empty($units)) {
    $config->units = $units;
  }
  if (isset($strokeWeight) && is_numeric($strokeWeight)) {
    $config->strokeWeight = (int) $strokeWeight;
  }
  if (isset($strokeColor) && !empty($strokeColor)) {
    $config->strokeColor = $strokeColor;
  }
  if (isset($strokeOpacity) && is_numeric($strokeOpacity)) {
    $config->strokeOpacity = $strokeOpacity;
  }
  if ($config->save() !== true) {
    echo "Configuration migration failed. Please update your settings manually from web interface" . PHP_EOL;
    return false;
  }
  echo "Configuration successfully migrated to database" . PHP_EOL;
  return true;
}

/**
 * Get queries array for current db driver
 * @return array Queries
 */
function getQueries() {
  $dbDriver = uDb::getInstance()->getAttribute(PDO::ATTR_DRIVER_NAME);
  $tConfig = uDb::getInstance()->table('config');
  $tLayers = uDb::getInstance()->table('ol_layers');
  $tUsers = uDb::getInstance()->table('users');
  $tTracks = uDb::getInstance()->table('tracks');
  $tPositions = uDb::getInstance()->table('positions');
  $tPositionsBackup = "{$tPositions}_backup";

  $queries = [];
  switch ($dbDriver) {
    case "mysql":
      $queries[] = "CREATE TABLE `$tConfig` (
          `name` varchar(20) PRIMARY KEY,
          `value` tinyblob NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

      $queries[] = "INSERT INTO `$tConfig` (`name`, `value`) VALUES
          ('color_extra', 's:7:\"#cccccc\";'),
          ('color_hilite', 's:7:\"#feff6a\";'),
          ('color_normal', 's:7:\"#ffffff\";'),
          ('color_start', 's:7:\"#55b500\";'),
          ('color_stop', 's:7:\"#ff6a00\";'),
          ('google_key', 's:0:\"\";'),
          ('interval_seconds', 'i:10;'),
          ('lang', 's:2:\"en\";'),
          ('latitude', 'd:52.229999999999997;'),
          ('longitude', 'd:21.010000000000002;'),
          ('map_api', 's:10:\"openlayers\";'),
          ('pass_lenmin', 'i:10;'),
          ('pass_strength', 'i:2;'),
          ('public_tracks', 'b:1;'),
          ('require_auth', 'b:1;'),
          ('stroke_color', 's:7:\"#ff0000\";'),
          ('stroke_opacity', 'd:1;'),
          ('stroke_weight', 'i:2;'),
          ('units', 's:6:\"metric\";'),
          ('upload_maxsize', 'i:0;')";

      $queries[] = "CREATE TABLE `$tLayers` (
          `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `name` varchar(50) NOT NULL,
          `url` varchar(255) NOT NULL,
          `priority` int(11) NOT NULL DEFAULT '0'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

      $queries[] = "INSERT INTO `$tLayers` (`id`, `name`, `url`, `priority`) VALUES
          (1, 'OpenCycleMap', 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png', 0),
          (2, 'OpenTopoMap', 'https://{a-c}.tile.opentopomap.org/{z}/{x}/{y}.png', 0),
          (3, 'OpenSeaMap', 'https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', 0),
          (4, 'ESRI', 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 0),
          (5, 'UMP', 'http://{1-3}.tiles.ump.waw.pl/ump_tiles/{z}/{x}/{y}.png', 0),
          (6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0);
      ";

      $queries[] = "ALTER TABLE `$tUsers` ADD `admin` BOOLEAN NOT NULL DEFAULT FALSE AFTER `password`";

      $queries[] = "ALTER TABLE `$tPositions` CHANGE `image_id` `image` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";

      break;

    case "pgsql":
      $queries[] = "CREATE TABLE $tConfig (
          name varchar(20) PRIMARY KEY,
          value bytea NOT NULL
      )";

      $queries[] = "INSERT INTO $tConfig (name, value) VALUES
          ('color_extra', 's:7:\"#cccccc\";'),
          ('color_hilite', 's:7:\"#feff6a\";'),
          ('color_normal', 's:7:\"#ffffff\";'),
          ('color_start', 's:7:\"#55b500\";'),
          ('color_stop', 's:7:\"#ff6a00\";'),
          ('google_key', 's:0:\"\";'),
          ('interval_seconds', 'i:10;'),
          ('lang', 's:2:\"en\";'),
          ('latitude', 'd:52.229999999999997;'),
          ('longitude', 'd:21.010000000000002;'),
          ('map_api', 's:10:\"openlayers\";'),
          ('pass_lenmin', 'i:10;'),
          ('pass_strength', 'i:2;'),
          ('public_tracks', 'b:1;'),
          ('require_auth', 'b:1;'),
          ('stroke_color', 's:7:\"#ff0000\";'),
          ('stroke_opacity', 'd:1;'),
          ('stroke_weight', 'i:2;'),
          ('units', 's:6:\"metric\";'),
          ('upload_maxsize', 'i:0;')";

      $queries[] = "CREATE TABLE $tLayers (
          id serial PRIMARY KEY,
          name varchar(50) NOT NULL,
          url varchar(255) NOT NULL,
          priority int NOT NULL DEFAULT '0'
      )";

      $queries[] = "INSERT INTO $tLayers (id, name, url, priority) VALUES
          (1, 'OpenCycleMap', 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png', 0),
          (2, 'OpenTopoMap', 'https://{a-c}.tile.opentopomap.org/{z}/{x}/{y}.png', 0),
          (3, 'OpenSeaMap', 'https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', 0),
          (4, 'ESRI', 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 0),
          (5, 'UMP', 'http://{1-3}.tiles.ump.waw.pl/ump_tiles/{z}/{x}/{y}.png', 0),
          (6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0);
      ";

      $queries[] = "ALTER TABLE $tUsers ADD COLUMN admin boolean NOT NULL DEFAULT FALSE";
      $queries[] = "ALTER TABLE $tPositions DROP COLUMN image_id";
      $queries[] = "ALTER TABLE $tPositions ADD COLUMN image varchar(100) NULL DEFAULT NULL";

      break;

    case "sqlite":
      $queries[] = "CREATE TABLE `$tConfig` (
          `name` varchar(20) PRIMARY KEY,
          `value` tinyblob NOT NULL
      )";

      $queries[] = "INSERT INTO `$tConfig` (`name`, `value`) VALUES
          ('color_extra', 's:7:\"#cccccc\";'),
          ('color_hilite', 's:7:\"#feff6a\";'),
          ('color_normal', 's:7:\"#ffffff\";'),
          ('color_start', 's:7:\"#55b500\";'),
          ('color_stop', 's:7:\"#ff6a00\";'),
          ('google_key', 's:0:\"\";'),
          ('interval_seconds', 'i:10;'),
          ('lang', 's:2:\"en\";'),
          ('latitude', 'd:52.229999999999997;'),
          ('longitude', 'd:21.010000000000002;'),
          ('map_api', 's:10:\"openlayers\";'),
          ('pass_lenmin', 'i:10;'),
          ('pass_strength', 'i:2;'),
          ('public_tracks', 'b:1;'),
          ('require_auth', 'b:1;'),
          ('stroke_color', 's:7:\"#ff0000\";'),
          ('stroke_opacity', 'd:1;'),
          ('stroke_weight', 'i:2;'),
          ('units', 's:6:\"metric\";'),
          ('upload_maxsize', 'i:0;')";

      $queries[] = "CREATE TABLE `$tLayers` (
          `id` integer PRIMARY KEY AUTOINCREMENT,
          `name` varchar(50) NOT NULL,
          `url` varchar(255) NOT NULL,
          `priority` integer NOT NULL DEFAULT '0'
      )";

      $queries[] = "INSERT INTO `$tLayers` (`id`, `name`, `url`, `priority`) VALUES
          (1, 'OpenCycleMap', 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png', 0),
          (2, 'OpenTopoMap', 'https://{a-c}.tile.opentopomap.org/{z}/{x}/{y}.png', 0),
          (3, 'OpenSeaMap', 'https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', 0),
          (4, 'ESRI', 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 0),
          (5, 'UMP', 'http://{1-3}.tiles.ump.waw.pl/ump_tiles/{z}/{x}/{y}.png', 0),
          (6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0);
      ";

      $queries[] = "ALTER TABLE `$tUsers` ADD `admin` boolean NOT NULL DEFAULT FALSE";

      $queries[] = "PRAGMA foreign_keys=OFF";
      $queries[] = "CREATE TABLE `$tPositionsBackup` (
          `id` integer PRIMARY KEY AUTOINCREMENT,
          `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `user_id` integer NOT NULL,
          `track_id` integer NOT NULL,
          `latitude` double NOT NULL,
          `longitude` double NOT NULL,
          `altitude` double DEFAULT NULL,
          `speed` double DEFAULT NULL,
          `bearing` double DEFAULT NULL,
          `accuracy` integer DEFAULT NULL,
          `provider` varchar(100) DEFAULT NULL,
          `comment` varchar(255) DEFAULT NULL,
          `image` varchar(100) DEFAULT NULL,
          FOREIGN KEY(`user_id`) REFERENCES `$tUsers`(`id`),
          FOREIGN KEY(`track_id`) REFERENCES `$tTracks`(`id`)
      )";
      $queries[] = "INSERT INTO `$tPositionsBackup` SELECT id, time, user_id, track_id, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, NULL FROM positions";
      $queries[] = "DROP TABLE `$tPositions`";
      $queries[] = "ALTER TABLE `$tPositionsBackup` RENAME TO `$tPositions`";
      $queries[] = "CREATE INDEX `idx_ptrack_id` ON `$tPositions`(`track_id`)";
      $queries[] = "CREATE INDEX `idx_puser_id` ON `$tPositions`(`user_id`)";
      $queries[] = "PRAGMA foreign_keys=ON";

      break;

    default:
      throw new InvalidArgumentException("Driver not supported");
  }
  return $queries;
}

?>
