<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
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

// This script is disabled by default. Change below to true before running.
$enabled = false;


/* -------------------------------------------- */
/* no user modifications should be needed below */

/** @noinspection ConstantCanBeUsedInspection */
if (version_compare(PHP_VERSION, "5.5.0", "<")) {
  die("Sorry, ulogger will not work with PHP version lower than 5.5 (you have " . PHP_VERSION . ")");
}

define("ROOT_DIR", dirname(__DIR__));
$dbConfig = ROOT_DIR . "/config.php";
$dbConfigLoaded = false;
$configDSN = "";
$configUser = "";
$configPass = "";
$configPrefix = "";
if (file_exists($dbConfig)) {
  /** @noinspection PhpIncludeInspection */
  include($dbConfig);
  $dbConfigLoaded = true;
  if (isset($dbdsn)) { $configDSN = $dbdsn; }
  if (isset($dbuser)) { $configUser = $dbuser; }
  if (isset($dbpass)) { $configPass = $dbpass; }
  if (isset($dbprefix)) { $configPrefix = $dbprefix; }
}
require_once(ROOT_DIR . "/helpers/db.php");
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");
require_once(ROOT_DIR . "/helpers/user.php");
require_once(ROOT_DIR . "/helpers/utils.php");

$command = uUtils::postString("command");
$language = uUtils::getString("lang", "en");

$config = uConfig::getOfflineInstance();
$config->lang = $language;
$lang = (new uLang($config))->getStrings();
$langSetup = (new uLang($config))->getSetupStrings();

$prefix = preg_replace("/[^a-z0-9_]/i", "", $configPrefix);
$tPositions = $prefix . "positions";
$tTracks = $prefix . "tracks";
$tUsers = $prefix . "users";
$tConfig = $prefix . "config";
$tLayers = $prefix . "ol_layers";

$messages = [];

switch ($command) {
  case "setup":

    $error = false;
    try {
      $pdo = getPdo();
    } catch (PDOException $e) {
      $messages[] = "<span class=\"warn\">{$langSetup["dbconnectfailed"]}</span>";
      $messages[] = sprintf($langSetup["serversaid"], "<b>" . htmlentities($e->getMessage()) . "</b>");
      $messages[] = $langSetup["checkdbsettings"];
      break;
    }
    try {
      $queries = getQueries($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
      $pdo->beginTransaction();
      foreach ($queries as $query) {
        $pdo->exec($query);
      }
      $pdo->commit();
    } catch (PDOException $e) {
      $pdo->rollBack();
      $messages[] = "<span class=\"warn\">{$langSetup["dbqueryfailed"]}</span>";
      $messages[] = sprintf($langSetup["serversaid"], "<b>" . htmlentities($e->getMessage()) . "</b>");
      $error = true;
    }
    $pdo = null;
    if (!$error) {
      $messages[] = "<span class=\"ok\">{$langSetup["dbtablessuccess"]}</span>";
      $messages[] = $langSetup["setupuser"];
      $form = "<form id=\"userForm\" method=\"post\" action=\"setup.php?lang=$language\" onsubmit=\"return validateForm()\"><input type=\"hidden\" name=\"command\" value=\"adduser\">";
      $form .= "<label><b>{$lang["username"]}</b></label><input type=\"text\" placeholder=\"{$lang["usernameenter"]}\" name=\"login\" required>";
      $form .= "<label><b>{$lang["password"]}</b></label><input type=\"password\" placeholder=\"{$lang["passwordenter"]}\" name=\"pass\" required>";
      $form .= "<label><b>{$lang["passwordrepeat"]}</b></label><input type=\"password\" placeholder=\"{$lang["passwordenter"]}\" name=\"pass2\" required>";
      $form .= "<div class=\"buttons\"><button type=\"submit\">{$lang["submit"]}</button></div>";
      $form .= "</form>";
      $messages[] = $form;
    }
    break;

  case "adduser":
    $config->save();
    $login = uUtils::postString("login");
    $pass = uUtils::postPass("pass");

    if (uUser::add($login, $pass, true) !== false) {
      $messages[] = "<span class=\"ok\">{$langSetup["congratulations"]}</span>";
      $messages[] = $langSetup["setupcomplete"];
      $messages[] = "<span class=\"warn\">{$langSetup["disablewarn"]}</span><br>";
      $messages[] = sprintf($langSetup["disabledesc"], "<b>\$enabled</b>", "<b>false</b>");
    } else {
      $messages[] = "<span class=\"warn\">{$langSetup["setupfailed"]}</span>";
    }
    break;

  default:
    $langsArr = uLang::getLanguages();
    $langsOpts = "";
    foreach ($langsArr as $langCode => $langName) {
      $langsOpts .= "<option value=\"$langCode\"" . ($config->lang === $langCode ? " selected" : "") . ">$langName</option>";
    }
    $messages[] = "<div id=\"language\">
      <label for=\"lang\">{$lang['language']}</label>
      <select id=\"lang\" name=\"lang\" onchange=\"return changeLang(this)\">
        $langsOpts
      </select>
    </div>";
    $messages[] = "<img src=\"../icons/favicon-32x32.png\" alt=\"µLogger\">" . $langSetup["welcome"];
    if (!isset($enabled) || $enabled === false) {
      $messages[] = sprintf($langSetup["disabledwarn"], "<b>\$enabled</b>", "<b>true</b>");
      $messages[] = sprintf($langSetup["lineshouldread"], "<br><span class=\"warn\">\$enabled = false;</span><br>", "<br><span class=\"ok\">\$enabled = true;</span>");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (!$dbConfigLoaded) {
      $messages[] = $langSetup["createconfig"];
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (ini_get("session.auto_start") === "1") {
      $messages[] = sprintf($langSetup["optionwarn"], "session.auto_start", "0 (off)");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (!extension_loaded("pdo")) {
      $messages[] = sprintf($langSetup["extensionwarn"], "PDO");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (empty($configDSN)) {
      $messages[] = sprintf($langSetup["nodbsettings"], "\$dbdsn");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    try {
      $pdo = getPdo();
    } catch (PDOException $e) {
      $isSqlite = stripos($configDSN, "sqlite") === 0;
      if (!$isSqlite && empty($configUser)) {
        $messages[] = sprintf($langSetup["nodbsettings"], "\$dbuser, \$dbpass");
      } else {
        $messages[] = $langSetup["dbconnectfailed"];
        $messages[] = $langSetup["checkdbsettings"];
        $messages[] = sprintf($langSetup["serversaid"], "<b>" . htmlentities($e->getMessage()) . "</b>");
      }
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    $pdo = null;
    $dbName = uDb::getDbName($configDSN);
    $dbName = empty($dbName) ? '""' : "<b>" . htmlentities($dbName) . "</b>";
    $messages[] = sprintf($langSetup["scriptdesc"], "'$tPositions', '$tTracks', '$tUsers'", $dbName);
    $messages[] = $langSetup["scriptdesc2"];
    $messages[] = "<form method=\"post\" action=\"setup.php?lang=$language\"><input type=\"hidden\" name=\"command\" value=\"setup\"><button>{$langSetup["startbutton"]}</button></form>";
    break;
}

/**
 * @param string $dbDriver
 * @return array
 */
function getQueries($dbDriver) {
  global $tPositions, $tUsers, $tTracks, $tConfig, $tLayers;

  $queries = [];
  switch ($dbDriver) {
    case "mysql":
      $queries[] = "DROP TABLE IF EXISTS `$tPositions`";
      $queries[] = "DROP TABLE IF EXISTS `$tTracks`";
      $queries[] = "DROP TABLE IF EXISTS `$tUsers`";
      $queries[] = "DROP TABLE IF EXISTS `$tConfig`";
      $queries[] = "DROP TABLE IF EXISTS `$tLayers`";

      $queries[] = "CREATE TABLE `$tUsers` (
                      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                      `login` varchar(15) CHARACTER SET latin1 NOT NULL UNIQUE,
                      `password` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
                      `admin` boolean NOT NULL DEFAULT FALSE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";


      $queries[] = "CREATE TABLE `$tTracks` (
                      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                      `user_id` int(11) NOT NULL,
                      `name` varchar(255) DEFAULT NULL,
                      `comment` varchar(1024) DEFAULT NULL,
                      INDEX `idx_user_id` (`user_id`),
                      FOREIGN KEY(`user_id`) REFERENCES `$tUsers`(`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

      $queries[] = "CREATE TABLE `$tPositions` (
                      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                      `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `user_id` int(11) NOT NULL,
                      `track_id` int(11) NOT NULL,
                      `latitude` double NOT NULL,
                      `longitude` double NOT NULL,
                      `altitude` double DEFAULT NULL,
                      `speed` double DEFAULT NULL,
                      `bearing` double DEFAULT NULL,
                      `accuracy` int(11) DEFAULT NULL,
                      `provider` varchar(100) DEFAULT NULL,
                      `comment` varchar(255) DEFAULT NULL,
                      `image` varchar(100) DEFAULT NULL,
                      INDEX `idx_track_id` (`track_id`),
                      INDEX `idx_user_id` (`user_id`),
                      FOREIGN KEY(`user_id`) REFERENCES `$tUsers`(`id`),
                      FOREIGN KEY(`track_id`) REFERENCES `$tTracks`(`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

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
                    ('units', 's:6:\"metric\";');";

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
                    (6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0)";

      break;

    case "pgsql":
      $queries[] = "DROP TABLE IF EXISTS $tPositions";
      $queries[] = "DROP TABLE IF EXISTS $tTracks";
      $queries[] = "DROP TABLE IF EXISTS $tUsers";
      $queries[] = "DROP TABLE IF EXISTS $tConfig";
      $queries[] = "DROP TABLE IF EXISTS $tLayers";

      $queries[] = "CREATE TABLE $tUsers (
                      id serial PRIMARY KEY,
                      login varchar(15) NOT NULL UNIQUE,
                      password varchar(255) NOT NULL DEFAULT '',
                      admin boolean NOT NULL DEFAULT FALSE
                    )";

      $queries[] = "CREATE TABLE $tTracks (
                      id serial PRIMARY KEY,
                      user_id int NOT NULL,
                      name varchar(255) DEFAULT NULL,
                      comment varchar(1024) DEFAULT NULL,
                      FOREIGN KEY(user_id) REFERENCES $tUsers(id)
                    )";
      $queries[] = "CREATE INDEX idx_user_id ON $tTracks(user_id)";

      $queries[] = "CREATE TABLE $tPositions (
                      id serial PRIMARY KEY,
                      time timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      user_id int NOT NULL,
                      track_id int NOT NULL,
                      latitude double precision NOT NULL,
                      longitude double precision NOT NULL,
                      altitude double precision DEFAULT NULL,
                      speed double precision DEFAULT NULL,
                      bearing double precision DEFAULT NULL,
                      accuracy int DEFAULT NULL,
                      provider varchar(100) DEFAULT NULL,
                      comment varchar(255) DEFAULT NULL,
                      image varchar(100) DEFAULT NULL,
                      FOREIGN KEY(user_id) REFERENCES $tUsers(id),
                      FOREIGN KEY(track_id) REFERENCES $tTracks(id)
                    )";
      $queries[] = "CREATE INDEX idx_ptrack_id ON $tPositions(track_id)";
      $queries[] = "CREATE INDEX idx_puser_id ON $tPositions(user_id)";

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
                    ('units', 's:6:\"metric\";')";

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
                    (6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0)";

      break;

    case "sqlite":
      $queries[] = "DROP TABLE IF EXISTS `$tPositions`";
      $queries[] = "DROP TABLE IF EXISTS `$tTracks`";
      $queries[] = "DROP TABLE IF EXISTS `$tUsers`";
      $queries[] = "DROP TABLE IF EXISTS `$tConfig`";
      $queries[] = "DROP TABLE IF EXISTS `$tLayers`";

      $queries[] = "CREATE TABLE `$tUsers` (
                    `id` integer PRIMARY KEY AUTOINCREMENT,
                    `login` varchar(15) NOT NULL UNIQUE,
                    `password` varchar(255) NOT NULL DEFAULT '',
                    `admin` integer NOT NULL DEFAULT 0
                  )";
      $queries[] = "CREATE TABLE `$tTracks` (
                   `id` integer PRIMARY KEY AUTOINCREMENT,
                   `user_id` integer NOT NULL,
                   `name` varchar(255) DEFAULT NULL,
                   `comment` varchar(1024) DEFAULT NULL,
                   FOREIGN KEY(`user_id`) REFERENCES `$tUsers`(`id`)
                 )";
      $queries[] = "CREATE INDEX `idx_user_id` ON `$tTracks`(`user_id`)";

      $queries[] = "CREATE TABLE `$tPositions` (
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
      $queries[] = "CREATE INDEX `idx_ptrack_id` ON `$tPositions`(`track_id`)";
      $queries[] = "CREATE INDEX `idx_puser_id` ON `$tPositions`(`user_id`)";

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
                    ('units', 's:6:\"metric\";');";

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
                    (6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0)";

      break;

    default:
      throw new InvalidArgumentException("Driver not supported");
  }
  return $queries;
}

/**
 * @return PDO
 * @throws PDOException
 */
function getPdo() {
  global $configDSN, $configUser, $configPass;
  $options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
  return new PDO($configDSN, $configUser, $configPass, $options);
}

?>

<!DOCTYPE html>
<html lang="<?= $language ?>">
<head>
  <title><?= $lang["title"] ?></title>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
  <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&amp;subset=cyrillic" rel="stylesheet">
  <link href="../css/main.css" type="text/css" rel="stylesheet">
  <style>
    #message {
      font-family: 'Open Sans', Verdana, sans-serif;
      font-size: 1.2em;
      color: #f8f5f7;
      padding: 10%;
    }

    #message img {
      vertical-align: bottom;
    }

    #message input[type=text], #message input[type=password] {
      width: 40em;
      padding: 0.4em;
      margin: 0.8em 0;
      display: block;
      border: 1px solid #ccc;
      box-sizing: border-box;
      border-radius: 5px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
    }

    #language {
      text-align: right;
    }

    #language label {
      font-size: small;
    }

    .warn {
      color: #ffc747;
    }

    .ok {
      color: #00e700;
    }
  </style>
  <!--suppress ES6ConvertVarToLetConst -->
  <script>
    var lang = <?= json_encode($lang) ?>;

    function validateForm() {
      var form = document.getElementById('userForm');
      var login = form.elements['login'].value.trim();
      var pass = form.elements['pass'].value;
      var pass2 = form.elements['pass2'].value;
      if (!login || !pass || !pass2) {
        alert(lang['allrequired']);
        return false;
      }
      if (pass !== pass2) {
        alert(lang['passnotmatch']);
        return false;
      }
      return true;
    }

    function changeLang(el) {
      window.location = '?lang=' + el.value;
      return false;
    }
  </script>
</head>

<body>
<div id="message">
  <?php foreach ($messages as $message): ?>
    <p><?= $message ?></p>
  <?php endforeach; ?>
</div>
</body>
</html>