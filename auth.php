<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
require_once("config.php");
// if is set cookie overwrite config value
if (isset($_COOKIE["ulogger_api"])) { $mapapi = $_COOKIE["ulogger_api"]; }
if (isset($_COOKIE["ulogger_lang"])) { $lang = $_COOKIE["ulogger_lang"]; }
if (isset($_COOKIE["ulogger_units"])) { $units = $_COOKIE["ulogger_units"]; }
if (isset($_COOKIE["ulogger_interval"])) { $interval = $_COOKIE["ulogger_interval"]; }
require_once("lang.php");
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
  if (defined('headless')) {
    header('HTTP/1.1 503 Service Unavailable', true, 503);
  } else {
    printf("Connect failed: %s\n", $mysqli->connect_error);
  }
  exit();
}
$mysqli->set_charset("utf8");
$auth = NULL;
$admin = NULL;
if ($require_authentication || defined('headless')) {
  /* authentication */
  session_name('ulogger');
  session_start();
  $sid = session_id();

  $auth = (isset($_SESSION['auth']) ? $_SESSION['auth'] : "");
  $admin = (isset($_SESSION['admin']) ? $_SESSION['admin'] : "");
  $user = (isset($_REQUEST['user']) ? $_REQUEST['user'] : "");
  $pass = (isset($_REQUEST['pass']) ? $_REQUEST['pass'] : "");
  $ssl = ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "" || $_SERVER['HTTPS'] == "off") ? "http" : "https");
  $auth_error = (isset($_REQUEST['auth_error']) ? $_REQUEST['auth_error'] : 0);

  // not authenticated and username not submited
  // load form
  if ((!$auth) && (!$user)){
    if (defined('headless')) {
      header('HTTP/1.1 401 Unauthorized', true, 401);
    } else {
      print
    '<!DOCTYPE html>
    <html>
      <head>
        <title>'.$lang_title.'</title>
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
          <div id="title">'.$lang_title.'</div>
          <div id="subtitle">'.$lang_private.'</div>
          <form action="index.php" method="post">
          '.$lang_username.':<br />
          <input type="text" name="user"><br />
          '.$lang_password.':<br />
          <input type="password" name="pass"><br />
          <br />
          <input type="submit" value="'.$lang_login.'">
          </form>
          <div id="error">'.(($auth_error==1) ? $lang_authfail : "").'</div>
        </div>
      </body>
    </html>';
    }
    $mysqli->close();
    exit();
  }

  // username submited
  if ((!$auth) && ($user)){
    $query = $mysqli->prepare("SELECT id, login, password FROM users WHERE login=? LIMIT 1");
    $query->bind_param('s', $user);
    $query->execute();
    $query->bind_result($rec_ID, $rec_user, $rec_pass);
    $query->fetch();
    $query->free_result();
    //correct pass

    if (($user == $rec_user) && password_verify($pass, $rec_pass)) {
      // login successful
      //delete old session
      $_SESSION = NULL;
      session_destroy();
      // start new session
      session_name('ulogger');
      session_start();
      if (($user == $admin_user) && !empty($admin_user)) {
          $_SESSION['admin'] = $admin_user;
      }
      $_SESSION['auth'] = $rec_ID;
      if (defined('headless')) {
        $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/client/index.php");
        header("Location: $ssl://$url");
      } else {
        $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/index.php");
        header("Location: $ssl://$url");
      }
      exit();
    } else {
      // unsuccessful
      $error = "?auth_error=1";
      // destroy session
      $_SESSION = NULL;
      if (isset($_COOKIE[session_name('ulogger')])) {
        setcookie(session_name('ulogger'),'',time()-42000,'/');
      }
      session_destroy();
      $mysqli->close();
      if (defined('headless')) {
        header('HTTP/1.1 401 Unauthorized', true, 401);
      } else {
        $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/index.php");
        header("Location: $ssl://$url$error");
      }
      exit();
    }
  }
  /* end of authentication */
}
?>
