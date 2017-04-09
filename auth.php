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

require_once("helpers/config.php");
$config = new uConfig();

require_once("lang.php");
require_once("helpers/db.php");
$mysqli = uDb::getInstance();
require_once($config::$rootDir . "/helpers/user.php");

session_name('ulogger');
session_start();
$sid = session_id();

// check for forced login to authorize admin in case of public access
$force_login = (isset($_REQUEST['force_login']) ? $_REQUEST['force_login'] : false);
if ($force_login) {
  $config::$require_authentication = true;
}

$user = new uUser();
$user->getFromSession();
if (!$user->isValid && ($config::$require_authentication || defined('headless'))) {
  /* authentication */
  $login = (isset($_REQUEST['user']) ? $_REQUEST['user'] : NULL);
  $pass = (isset($_REQUEST['pass']) ? $_REQUEST['pass'] : NULL);
  $ssl = ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "" || $_SERVER['HTTPS'] == "off") ? "http" : "https");
  $auth_error = (isset($_REQUEST['auth_error']) ? $_REQUEST['auth_error'] : 0);

  if (!$login){
    // not authenticated and username not submited
    // load form
    if (defined('headless')) {
      header('HTTP/1.1 401 Unauthorized', true, 401);
    } else {
      print
    '<!DOCTYPE html>
    <html>
      <head>
        <title>'.$lang["title"].'</title>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" type="text/css" href="main.css">
        <script type="text/javascript">
        function focus() {
          document.forms[0].elements[0].focus();
        }
        </script>
      </head>
      <body onload="focus()">
        <div id="login">
          <div id="title">'.$lang["title"].'</div>
          <div id="subtitle">'.$lang["private"].'</div>
          <form action="index.php" method="post">
          '.$lang["username"].':<br />
          <input type="text" name="user"><br />
          '.$lang["password"].':<br />
          <input type="password" name="pass"><br />
          <br />
          <input type="submit" value="'.$lang["login"].'">
          '.(($force_login==1) ? "<input type=\"hidden\" name=\"force_login\" value=\"1\">" : "").'
          </form>
          <div id="error">'.(($auth_error==1) ? $lang["authfail"] : "").'</div>
        </div>
      </body>
    </html>';
    }
    $mysqli->close();
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
      $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/index.php");
      header("Location: $ssl://$url");
    } else {
      // unsuccessful
      $error = "?auth_error=1";
      // destroy session
      $_SESSION = NULL;
      if (isset($_COOKIE[session_name('ulogger')])) {
        setcookie(session_name('ulogger'),'',time()-42000,'/');
      }
      session_destroy();
      if (defined('headless')) {
        header('HTTP/1.1 401 Unauthorized', true, 401);
      } else {
        $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/index.php");
        header("Location: $ssl://$url$error");
      }
    }
    $mysqli->close();
    exit();
  }
  /* end of authentication */
}
?>
