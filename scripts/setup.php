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

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  die("Sorry, ulogger will not work with PHP version lower than 5.4 (you have " . PHP_VERSION . ")");
}

define("ROOT_DIR", dirname(__DIR__));
require_once(ROOT_DIR . "/helpers/user.php");
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/lang.php");

$command = isset($_REQUEST['command']) ? $_REQUEST['command'] : NULL;

$prefix = preg_replace('/[^a-z0-9_]/i', '', uConfig::$dbprefix);
$tPositions = $prefix . "positions";
$tTracks = $prefix . "tracks";
$tUsers = $prefix . "users";

$messages = [];
switch ($command) {
  case "setup":
    $queries = [];
    // positions
    $queries[] = "DROP TABLE IF EXISTS `$tPositions`";
    $queries[] = "CREATE TABLE `$tPositions` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
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
                    `image_id` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `index_trip_id` (`track_id`),
                    KEY `index_user_id` (`user_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    // tracks
    $queries[] = "DROP TABLE IF EXISTS `$tTracks`";
    $queries[] = "CREATE TABLE `$tTracks` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `name` varchar(255) DEFAULT NULL,
                    `comment` varchar(1024) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    // users
    $queries[] = "DROP TABLE IF EXISTS `$tUsers`";
    $queries[] = "CREATE TABLE `$tUsers` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `login` varchar(15) CHARACTER SET latin1 NOT NULL,
                    `password` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `login` (`login`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    $error = false;
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
      $mysqli = new mysqli(uConfig::$dbhost, uConfig::$dbuser, uConfig::$dbpass, uConfig::$dbname);
    } catch (mysqli_sql_exception $e ) {
      $messages[] = "<span class=\"warn\">{$langSetup["dbconnectfailed"]}</span>";
      $messages[] = sprintf($langSetup["serversaid"], "<b>" . $e->getMessage() . "</b>");
      $messages[] = $langSetup["checkdbsettings"];
      break;
    }
    try {
      $mysqli->set_charset('utf8');
      foreach ($queries as $query) {
        $mysqli->query($query);
      }
    } catch (mysqli_sql_exception $e) {
        $messages[] = "<span class=\"warn\">{$langSetup["dbqueryfailed"]}</span>";
        $messages[] = sprintf($langSetup["serversaid"], "<b>" . $e->getMessage() . "</b>");
        $error = true;
    }
    $mysqli->close();
    if (!$error) {
      $messages[] = "<span class=\"ok\">{$langSetup["dbtablessuccess"]}</span>";
      $messages[] = $langSetup["setupuser"];
      $form = "<form id=\"userForm\" method=\"post\" action=\"setup.php\" onsubmit=\"return validateForm()\"><input type=\"hidden\" name=\"command\" value=\"adduser\">";
      $form .= "<label><b>{$lang["username"]}</b></label><input type=\"text\" placeholder=\"{$lang["usernameenter"]}\" name=\"login\" required>";
      $form .= "<label><b>{$lang["password"]}</b></label><input type=\"password\" placeholder=\"{$lang["passwordenter"]}\" name=\"pass\" required>";
      $form .= "<label><b>{$lang["passwordrepeat"]}</b></label><input type=\"password\" placeholder=\"{$lang["passwordenter"]}\" name=\"pass2\" required>";
      $form .= "<div class=\"buttons\"><button type=\"submit\">{$lang["submit"]}</button></div>";
      $form .= "</form>";
      $messages[] = $form;
    }
    break;

  case "adduser":
      $login = isset($_REQUEST['login']) ? $_REQUEST['login'] : NULL;
      $pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : NULL;

      if (uUser::add($login, $pass) !== false) {
        $messages[] = "<span class=\"ok\">{$langSetup["congratulations"]}</span>";
        $messages[] = $langSetup["setupcomplete"];
        $messages[] = "<span class=\"warn\">{$langSetup["disablewarn"]}</span><br>";
        $messages[] = sprintf($langSetup["disabledesc"], "<b>\$enabled</b>", "<b>false</b>");
      } else {
        $messages[] = "<span class=\"warn\">{$langSetup["setupfailed"]}</span>";
      }
    break;

  default:
    $messages[] = "<img src=\"../icons/favicon-32x32.png\">" . $langSetup["welcome"];
    if (!isset($enabled) || $enabled === false) {
      $messages[] = sprintf($langSetup["disabledwarn"], "<b>\$enabled</b>", "<b>true</b>");
      $messages[] = sprintf($langSetup["lineshouldread"], "<br><span class=\"warn\">\$enabled = false;</span><br>", "<br><span class=\"ok\">\$enabled = true;</span>");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (!function_exists('password_hash')) {
      $messages[] = $langSetup["passfuncwarn"];
      $messages[] = $langSetup["passfunchack"];
      $messages[] = sprintf($langSetup["lineshouldread"], "<br><span class=\"warn\">//require_once(ROOT_DIR . \"/helpers/password.php\");</span><br>", "<br><span class=\"ok\">require_once(ROOT_DIR . \"/helpers/password.php\");</span>");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (!uConfig::isFileLoaded()) {
      $messages[] = $langSetup["createconfig"];
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    if (empty(uConfig::$dbname) || empty(uConfig::$dbhost) || empty(uConfig::$dbuser)) {
      $messages[] = sprintf($langSetup["nodbsettings"], "\$dbname, \$dbhost, \$dbuser, \$dbpass");
      $messages[] = $langSetup["dorestart"];
      $messages[] = "<form method=\"post\" action=\"setup.php\"><button>{$langSetup["restartbutton"]}</button></form>";
      break;
    }
    $messages[] = sprintf($langSetup["scriptdesc"], "'$tPositions', '$tTracks', '$tUsers'", "<b>" . uConfig::$dbname . "</b>");
    $messages[] = $langSetup["scriptdesc2"];
    $messages[] = "<form method=\"post\" action=\"setup.php\"><input type=\"hidden\" name=\"command\" value=\"setup\"><button>{$langSetup["startbutton"]}</button></form>";
    break;
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title><?= $lang["title"] ?></title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&amp;subset=cyrillic" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
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
      .warn {
        color: #ffc747;
      }
      .ok {
        color: #00e700;
      }
    </style>
    <script type="text/javascript">
      var lang = <?= json_encode($lang) ?>;
      var pass_regex = <?= uConfig::passRegex() ?>;

      function validateForm() {
        var form = document.getElementById('userForm');
        var login = form.elements['login'].value.trim();
        var pass = form.elements['pass'].value;
        var pass2 = form.elements['pass2'].value;
        if (!login || !pass || !pass2) {
          alert(lang['allrequired']);
          return false;
        }
        if (pass != pass2) {
          alert(lang['passnotmatch']);
          return false;
        }
        if (!pass_regex.test(pass)) {
          alert(lang['passlenmin'] + '\n' + lang['passrules']);
          return false;
        }
        return true;
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