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

/**
 * Set response error status and message
 *
 * @param array $response Respons
 * @param string $message Message
 */
function setError(&$response, $message) {
  $response['error'] = true;
  $response['message'] = $message;
}

define("headless", true);
define("client", true);
require_once(dirname(__DIR__) . "/auth.php"); // sets $user

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$response = [ 'error' => false ];

switch ($action) {
  // action: authorize
  case "auth":
    break;

  // action: adduser (currently unused)
  case "adduser":
    $login = isset($_REQUEST['login']) ? $_REQUEST['login'] : NULL;
    $pass = isset($_REQUEST['password']) ? $_REQUEST['password'] : NULL;
    if (!empty($login) && !empty($pass)) {
      $newUser = new uUser();
      $newId = $newUser->add($login, $pass);
      if ($newId !== false) {
        // return user id
        $response['userid'] = $newId;
      } else {
        setError($response, "Server error");
      }
    } else {
      setError($response, "Empty login or password");
    }
    break;

  // action: addtrack
  case "addtrack":
    $trackName = isset($_REQUEST['track']) ? $_REQUEST['track'] : NULL;
    if (empty($trackName)) {
      setError($response, "missing required parameter");
      break;
    }
    require_once(ROOT_DIR . "/helpers/track.php");
    $track = new uTrack();
    $trackId = $track->add($user->id, $trackName);
    if ($trackId === false) {
      setError($response, "Server error");
      break;
    }
    // return track id
    $response['trackid'] = $trackId;
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
    $imageId = isset($_REQUEST["imageid"]) ? $_REQUEST["imageid"] : NULL;
    $trackId = isset($_REQUEST["trackid"]) ? $_REQUEST["trackid"] : NULL;

    if (is_null($lat) || is_null($lon) || is_null($time) || is_null($trackId)) {
      setError($response, "missing required parameter");
      break;
    }

    require_once(ROOT_DIR . "/helpers/position.php");
    $position = new uPosition();
    $positionId = $position->add($user->id, $trackId,
            $time, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageId);

    if ($positionId === false) {
      setError($response, "Server error");
    }
    break;
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>