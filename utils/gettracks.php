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
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/track.php");

$auth = new uAuth();
$config = uConfig::getInstance();

$userId = uUtils::getInt('userid');

$tracksArr = [];
if ($userId) {
  if ($config->publicTracks ||
      ($auth->isAuthenticated() && ($auth->isAdmin() || $auth->user->id === $userId))) {
    $tracksArr = uTrack::getAll($userId);
  }
}

$result = [];
if ($tracksArr === false) {
  $result = [ "error" => true ];
} else if (!empty($tracksArr)) {
  foreach ($tracksArr as $track) {
    $result[] = [ "id" => $track->id, "name" => $track->name ];
  }
}
header("Content-type: application/json");
echo json_encode($result);
?>
