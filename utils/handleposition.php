<?php
/* μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
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

$action = uUtils::postString('action');
$positionId = uUtils::postInt('posid');
$comment = uUtils::postString('comment');

$config = uConfig::getInstance();
$lang = (new uLang($config))->getStrings();

if (empty($action) || empty($positionId)) {
  uUtils::exitWithError($lang["servererror"]);
}
$position = new uPosition($positionId);
if (!$position->isValid ||
  (!$auth->isAuthenticated() || (!$auth->isAdmin() && $auth->user->id !== $position->userId))) {
  uUtils::exitWithError($lang["servererror"]);
}

switch ($action) {

  case 'update':
    $position->comment = $comment;
    if ($position->update() === false) {
      uUtils::exitWithError($lang["servererror"]);
    }
    break;

  case 'delete':
    if ($position->delete() === false) {
      uUtils::exitWithError($lang["servererror"]);
    }
    break;

  default:
    uUtils::exitWithError($lang["servererror"]);
    break;
}

uUtils::exitWithSuccess();

?>