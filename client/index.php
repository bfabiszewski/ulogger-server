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

$action = uUtils::postString('action');

$auth = new uAuth();
if ($action !== "auth" && !$auth->isAuthenticated()) {
  $auth->sendUnauthorizedHeader();
  exitWithError("Unauthorized");
}

switch ($action) {
  // action: authorize
  case "auth":
    $login = uUtils::postString('user');
    $pass = uUtils::postPass('pass');
    if ($auth->checkLogin($login, $pass)) {
      exitWithSuccess();
    } else {
      $auth->sendUnauthorizedHeader();
      exitWithError("Unauthorized");
    }
    break;

  // action: adduser (currently unused)
  case "adduser":
    if (!$auth->user->isAdmin) {
      exitWithError("Not allowed");
    }
    $login = uUtils::postString('login');
    $pass = uUtils::postPass('password');
    if (empty($login) || empty($pass)) {
      exitWithError("Empty login or password");
    }
    $newId = uUser::add($login, $pass);
    if ($newId === false) {
      exitWithError("Server error");
    }
    exitWithSuccess(['userid' => $newId]);
    break;

  // action: addtrack
  case "addtrack":
    $trackName = uUtils::postString('track');
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
    $lat = uUtils::postFloat('lat');
    $lon = uUtils::postFloat('lon');
    $timestamp = uUtils::postInt('time');
    $altitude = uUtils::postFloat('altitude');
    $speed = uUtils::postFloat('speed');
    $bearing = uUtils::postFloat('bearing');
    $accuracy = uUtils::postInt('accuracy');
    $provider = uUtils::postString('provider');
    $comment = uUtils::postString('comment');
    $imageMeta = uUtils::requestFile('image');
    $trackId = uUtils::postInt('trackid');

    if (!is_float($lat) || !is_float($lon) || !is_int($timestamp) || !is_int($trackId)) {
      exitWithError("Missing required parameter");
    }

    $image = null;
    if (!empty($imageMeta)) {
      $image = uUpload::add($imageMeta, $trackId);
    }

    require_once(ROOT_DIR . "/helpers/position.php");
    $positionId = uPosition::add($auth->user->id, $trackId,
      $timestamp, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $image);

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