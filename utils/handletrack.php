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
require_once(ROOT_DIR . "/helpers/lang.php");
require_once(ROOT_DIR . "/helpers/track.php");
require_once(ROOT_DIR . "/helpers/utils.php");
require_once(ROOT_DIR . "/helpers/config.php");

$auth = new uAuth();

$action = uUtils::postString("action");
$trackId = uUtils::postInt("trackid");
$trackName = uUtils::postString("trackname");

$config = uConfig::getInstance();
$lang = (new uLang($config))->getStrings();

if (empty($action) || empty($trackId)) {
  uUtils::exitWithError($lang["servererror"]);
}
$track = new uTrack($trackId);
if (!$track->isValid) {
  uUtils::exitWithError($lang["servererror"]);
}
if (($action === "getmeta" && !$auth->hasReadAccess($track->userId)) ||
  ($action !== "getmeta" && !$auth->hasReadWriteAccess($track->userId))) {
  uUtils::exitWithError($lang["notauthorized"]);
}

$result = null;

switch ($action) {

  case "update":
    if (empty($trackName) || $track->update($trackName) === false) {
      uUtils::exitWithError($lang["servererror"]);
    }
    break;

  case "delete":
    if ($track->delete() === false) {
      uUtils::exitWithError($lang["servererror"]);
    }
    break;

  case "getmeta":
    $result = [
      "id" => $track->id,
      "name" => $track->name,
      "userId" => $track->userId,
      "comment" => $track->comment
    ];
    break;

  default:
    uUtils::exitWithError($lang["servererror"]);
    break;
}

uUtils::exitWithSuccess($result);

?>