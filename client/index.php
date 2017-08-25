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
   * Exit with error status and message
   *
   * @param string $message Message
   */
  function exitWithError($message) {
    $response = [];
    $response['error'] = true;
    $response['message'] = $message;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
  }

  /**
   * Exit with success status
   *
   * @param array $params Optional params
   * @return void
   */
  function exitWithSuccess($params = []) {
    $response = [];
    $response['error'] = false;
    header('Content-Type: application/json');
    echo json_encode(array_merge($response, $params));
    exit();
  }

  require_once(dirname(__DIR__) . "/helpers/auth.php");

  $auth = new uAuth();
  if (!$auth->isAuthenticated()) {
    $auth->sendUnauthorizedHeader();
    exitWithError("Unauthorized");
  }

  $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

  switch ($action) {
    // action: authorize
    case "auth":
      exitWithSuccess();
      break;

    // action: adduser (currently unused)
    case "adduser":
      if (!$auth->user->isAdmin) {
        exitWithError("Not allowed");
      }
      $login = isset($_REQUEST['login']) ? $_REQUEST['login'] : NULL;
      $pass = isset($_REQUEST['password']) ? $_REQUEST['password'] : NULL;
      if (empty($login) || empty($pass)) {
        exitWithError("Empty login or password");
      }
      $newId = uUser::add($login, $pass);
      if ($newId === false) {
        exitWithError("Server error");
      }
      exitWithSuccess(['userid'=> $newId]);
      break;

    // action: addtrack
    case "addtrack":
      $trackName = isset($_REQUEST['track']) ? $_REQUEST['track'] : NULL;
      if (empty($trackName)) {
        exitWithError("Missing required parameter");
      }
      require_once(ROOT_DIR . "/helpers/track.php");
      $trackId = uTrack::add($auth->user->id, $trackName);
      if ($trackId === false) {
        exitWithError("Server error");
      }
      // return track id
      exitWithSuccess(['trackid' => $trackId]);
      break;

    // action: addposition
    case "addpos":
      $lat = isset($_REQUEST["lat"]) ? $_REQUEST["lat"] : NULL;
      $lon = isset($_REQUEST["lon"]) ? $_REQUEST["lon"] : NULL;
      $timestamp = isset($_REQUEST["time"]) ? $_REQUEST["time"] : NULL;
      $altitude = isset($_REQUEST["altitude"]) ? $_REQUEST["altitude"] : NULL;
      $speed = isset($_REQUEST["speed"]) ? $_REQUEST["speed"] : NULL;
      $bearing = isset($_REQUEST["bearing"]) ? $_REQUEST["bearing"] : NULL;
      $accuracy = isset($_REQUEST["accuracy"]) ? $_REQUEST["accuracy"] : NULL;
      $provider = isset($_REQUEST["provider"]) ? $_REQUEST["provider"] : NULL;
      $comment = isset($_REQUEST["comment"]) ? $_REQUEST["comment"] : NULL;
      $imageId = isset($_REQUEST["imageid"]) ? $_REQUEST["imageid"] : NULL;
      $trackId = isset($_REQUEST["trackid"]) ? $_REQUEST["trackid"] : NULL;

      if (!is_numeric($lat) || !is_numeric($lon) || !is_numeric($timestamp) || !is_numeric($trackId)) {
        exitWithError("Missing required parameter");
      }

      require_once(ROOT_DIR . "/helpers/position.php");
      $positionId = uPosition::add($auth->user->id, $trackId,
              $timestamp, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageId);

      if ($positionId === false) {
        exitWithError("Server error");
      }
      exitWithSuccess();
      break;

    default:
      exitWithError("Unknown command");
      break;
  }

?>