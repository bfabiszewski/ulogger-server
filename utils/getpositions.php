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

require_once(dirname(__DIR__) . "/helpers/auth.php");
require_once(ROOT_DIR . "/helpers/position.php");
require_once(ROOT_DIR . "/helpers/utils.php");

$auth = new uAuth();

$userId = uUtils::getInt('userid');
$trackId = uUtils::getInt('trackid');
$afterId = uUtils::getInt('afterid');
$last = uUtils::getInt('last');

$positionsArr = [];
if ($userId) {
  if (uConfig::$public_tracks ||
      ($auth->isAuthenticated() && ($auth->isAdmin() || $auth->user->id === $userId))) {
    if ($trackId) {
      // get all track data
      $positionsArr = uPosition::getAll($userId, $trackId, $afterId);
    } else if ($last) {
      // get data only for latest point
      $position = uPosition::getLast($userId);
      if ($position->isValid) {
        $positionsArr[] = $position;
      }
    }
  }
} else if ($last) {
  if (uConfig::$public_tracks || ($auth->isAuthenticated() && ($auth->isAdmin()))) {
    $positionsArr = uPosition::getLastAllUsers();
  }
}

$result = [];
if ($positionsArr === false) {
  $result = [ "error" => true ];
} else if (!empty($positionsArr)) {
  foreach ($positionsArr as $position) {
    $distance = !$last && isset($prevPosition) ? $position->distanceTo($prevPosition) : 0;
    $seconds = !$last && isset($prevPosition) ? $position->secondsTo($prevPosition) : 0;
    $result[] = [
      "id" => $position->id,
      "latitude" => $position->latitude,
      "longitude" => $position->longitude,
      "altitude" => ($position->altitude) ? round($position->altitude) : $position->altitude,
      "speed" => $position->speed,
      "bearing" => $position->bearing,
      "timestamp" => $position->timestamp,
      "accuracy" => $position->accuracy,
      "provider" => $position->provider,
      "comment" => $position->comment,
      "image" => $position->image,
      "username" => $position->userLogin,
      "trackid" => $position->trackId,
      "trackname" => $position->trackName,
      "distance" => round($distance),
      "seconds" => $seconds
    ];
    $prevPosition = $position;
  }
}
header("Content-type: application/json");
echo json_encode($result);

?>
