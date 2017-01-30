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

function setError(&$response, $message) {
  $response['error'] = true; 
  $response['message'] = $message;
}

define("headless", true); 
require_once("../auth.php");

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$userid = $_SESSION['auth'];

$response = [ 'error' => false ];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
  setError($response, $mysqli->error);
  $action = null;
}

switch ($action) {
  // action: authorize
  case "auth":
    break;

  // action: adduser
  case "adduser":
    $login = isset($_REQUEST['login']) ? $_REQUEST['login'] : NULL;
    $hash = isset($_REQUEST['password']) ? password_hash($_REQUEST['password'], PASSWORD_DEFAULT) : NULL;
    if (!empty($login) && !empty($hash)) {
      $sql = "INSERT INTO users (login, password) VALUES (?, ?)";
      $query = $mysqli->prepare($sql);
      $query->bind_param('ss', $login, $hash);
      $query->execute();
      $userid = $mysqli->insert_id;
      $query->close();
      if ($mysqli->errno) {
        setError($response, $mysqli->error);
        break;
      }
      // return user id
      $response['userid'] = $userid;
    } else {
      setError($response, "Empty login");
    }
    break;

  // action: addtrack
  case "addtrack":
    $trackname = isset($_REQUEST['track']) ? $_REQUEST['track'] : NULL;
    if (empty($trackname)) {
      setError($response, "missing required parameter");
      break;
    }
    $sql = "INSERT INTO tracks (user_id, name) VALUES (?, ?)";
    $query = $mysqli->prepare($sql);
    $query->bind_param('is', $userid, $trackname);
    $query->execute();
    $trackid = $mysqli->insert_id;
    $query->close();
    if ($mysqli->errno) {
      setError($response, $mysqli->error);
      break;
    }
    // return track id
    $response['trackid'] = $trackid;
    break;

  // action: addposition
  case "addpos":
    $lat = isset($_REQUEST["lat"]) ? $_REQUEST["lat"] : NULL;
    $lon = isset($_REQUEST["lon"]) ? $_REQUEST["lon"] : NULL;
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : NULL;
    $altitude = isset($_REQUEST["altitude"]) ? $_REQUEST["altitude"] : NULL;
    $speed = isset($_REQUEST["speed"]) ? $_REQUEST["speed"] : NULL;
    $bearing = isset($_REQUEST["bearing"]) ? $_REQUEST["bearing"] : NULL;
    $accuracy = isset($_REQUEST["accuracy"]) ? $_REQUEST["accuracy"] : NULL;
    $provider = isset($_REQUEST["provider"]) ? $_REQUEST["provider"] : NULL;
    $comment = isset($_REQUEST["comment"]) ? $_REQUEST["comment"] : NULL;
    $imageid = isset($_REQUEST["imageid"]) ? $_REQUEST["imageid"] : NULL;
    $trackid = isset($_REQUEST["trackid"]) ? $_REQUEST["trackid"] : NULL;

    if (is_null($lat) || is_null($lon) || is_null($time) || is_null($trackid)) {
      setError($response, "missing required parameter");
      break;
    }

    $sql = "INSERT INTO positions "
        ."(user_id, track_id,"
        ."time, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image_id)"
        ."VALUES (?,?,FROM_UNIXTIME(?),?,?,?,?,?,?,?,?,?)";

    $query = $mysqli->prepare($sql);
    $query->bind_param('iisddddddssi',
            $userid, $trackid,
            $time, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageid);
    $query->execute();
    $query->close();
    if ($mysqli->errno) {
      setError($response, $mysqli->error);
    }
    break;

}

$mysqli->close();

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>