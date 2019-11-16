<?php
/**
 * μlogger
 *
 * Copyright(C) 2019 Bartek Fabiszewski (www.fabiszewski.net)
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
require_once(ROOT_DIR . "/helpers/track.php");

$auth = new uAuth();

$usersArr = [];
if (uConfig::$public_tracks || $auth->isAdmin()) {
  $usersArr = uUser::getAll();
} else if ($auth->isAuthenticated()) {
  $usersArr = [ $auth->user ];
}

$result = [];
if ($usersArr === false) {
  $result = [ "error" => true ];
} else if (!empty($usersArr)) {
  foreach ($usersArr as $user) {
    $result[] = [ "id" => $user->id, "login" => $user->login ];
  }
}
header("Content-type: application/json");
echo json_encode($result);
?>
