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

if (defined('headless')) {
  ob_get_contents();
  ob_end_clean();
  error_reporting(0);
}
define('ROOT_DIR', __DIR__);
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/lang.php");
require_once(ROOT_DIR . "/helpers/user.php");

session_name('ulogger');
session_start();
$sid = session_id();

// check for forced login to authorize admin in case of public access
$force_login = isset($_REQUEST['force_login']) ? $_REQUEST['force_login'] : false;
if ($force_login) {
  uConfig::$require_authentication = true;
}

$user = new uUser();
$user->getFromSession();
if (!$user->isValid && (uConfig::$require_authentication || defined('client'))) {
  /* authentication */
  $login = isset($_REQUEST['user']) ? $_REQUEST['user'] : NULL;
  $pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : NULL;
  $ssl = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "" || $_SERVER['HTTPS'] == "off") ? "http" : "https";
  $auth_error = isset($_REQUEST['auth_error']) ? $_REQUEST['auth_error'] : false;

  if (!$login) {
    // not authenticated and username not submited
    // load form
    if (defined('headless')) {
      header('WWW-Authenticate: OAuth realm="users@ulogger"');
      header('HTTP/1.1 401 Unauthorized', true, 401);
    } else {
      print
    '<!DOCTYPE html>
    <html>
      <head>
        <title>' . $lang["title"] . '</title>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="icons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="icons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="manifest.json">
        <link rel="mask-icon" href="icons/safari-pinned-tab.svg" color="#5bbad5">
        <link rel="shortcut icon" href="icons/favicon.ico">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&amp;subset=cyrillic" rel="stylesheet">
        <meta name="msapplication-config" content="browserconfig.xml">
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <script type="text/javascript">
        function focus() {
          document.forms[0].elements[0].focus();
        }
        </script>
      </head>
      <body onload="focus()">
        <div id="login">
          <div id="title">' . $lang["title"] . '</div>
          <div id="subtitle">' . $lang["private"] . '</div>
          <form action="index.php" method="post">
          ' . $lang["username"] . ':<br>
          <input type="text" name="user"><br>
          ' . $lang["password"] . ':<br>
          <input type="password" name="pass"><br>
          <br>
          <input type="submit" value="' . $lang["login"] . '">
          ' . (($force_login) ? '<input type="hidden" name="force_login" value="1">
          <div id="cancel"><a href="index.php">' . $lang["cancel"] . '</a></div>' : '') . '
          </form>
          <div id="error">' . (($auth_error) ? $lang["authfail"] : "") . '</div>
        </div>
      </body>
    </html>';
    }
    exit();
  } else {
    // username submited
    $user = new uUser($login);

    //correct pass
    if ($user->isValid && $user->validPassword($pass)) {
      // login successful
      //delete old session
      $_SESSION = NULL;
      session_destroy();
      // start new session
      session_name('ulogger');
      session_start();
      $user->storeInSession();
      $url = str_replace("//", "/", $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/index.php");
      header("Location: $ssl://$url");
    } else {
      // unsuccessful
      $error = "?auth_error=1";
      if ($force_login) { $error .= "&force_login=1"; }
      // destroy session
      $_SESSION = NULL;
      if (isset($_COOKIE[session_name('ulogger')])) {
        setcookie(session_name('ulogger'), '', time() - 42000, '/');
      }
      session_destroy();
      if (defined('headless')) {
        header('WWW-Authenticate: OAuth realm="users@ulogger"');
        header('HTTP/1.1 401 Unauthorized', true, 401);
      } else {
        $url = str_replace("//", "/", $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/index.php");
        header("Location: $ssl://$url$error");
      }
    }
    exit();
  }
  /* end of authentication */
}
?>
