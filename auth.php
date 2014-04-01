<?php
/* phpTrackme
 *
 * Copyright(C) 2013 Bartek Fabiszewski (www.fabiszewski.net)
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
if (isset($_COOKIE["phpTrackme_api"])) { $mapapi = $_COOKIE["phpTrackme_api"]; }
if (isset($_COOKIE["phpTrackme_lang"])) { $lang = $_COOKIE["phpTrackme_lang"]; }
if (isset($_COOKIE["phpTrackme_units"])) { $units = $_COOKIE["phpTrackme_units"]; }
if (isset($_COOKIE["phpTrackme_interval"])) { $interval = $_COOKIE["phpTrackme_interval"]; }
require_once("lang.php");
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
$mysqli->set_charset("utf8");
$auth = NULL;
if ($require_authentication) {
  /* authentication */
  session_name('trackme');
  session_start();
  $sid = session_id();
  
  $auth = (isset($_SESSION['auth']) ? $_SESSION['auth'] : "");
  $user = (isset($_REQUEST['user']) ? $_REQUEST['user'] : "");
  $pass = (isset($_REQUEST['pass']) ? md5($salt.$_REQUEST['pass']) : "");
  $ssl = ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "" || $_SERVER['HTTPS'] == "off") ? "http" : "https");
  $auth_error = (isset($_REQUEST['auth_error']) ? $_REQUEST['auth_error'] : 0);
  
  // not authenticated and username not submited
  // load form
  if ((!$auth) && (!$user)){  
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
    $mysqli->close();
    exit;
  }

  // username submited
  if ((!$auth) && ($user)){
    $query = $mysqli->prepare("SELECT ID,username,password FROM users WHERE username=? LIMIT 1");
    $query->bind_param('s', $user);
    $query->execute();
    $query->bind_result($rec_ID, $rec_user, $rec_pass);
    $query->fetch();
    $query->free_result();
    //correct pass

    if (($user==$rec_user) && ($pass==$rec_pass)) { 
      // login successful
      //delete old session
      $_SESSION = NULL;
      session_destroy();  
      // start new session
      session_name('trackme');    
      session_start();
      if (($user==$admin_user) and ($admin_user != "")) {
          $_SESSION['auth'] = $admin_user;
      }
      else {
          $_SESSION['auth'] = $rec_ID;
      }
      $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/index.php");
      header("Location: $ssl://$url");
      exit;    
    } else {
      // unsuccessful
      $error = "?auth_error=1";
      // destroy session
      $_SESSION = NULL;
      if (isset($_COOKIE[session_name('trackme')])) {
        setcookie(session_name('trackme'),'',time()-42000,'/');
      }
      session_destroy();  
      $mysqli->close();    
      $url = str_replace("//", "/", $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/index.php");
      header("Location: $ssl://$url$error");
      exit;
    }
  }
  /* end of authentication */
}
?>
