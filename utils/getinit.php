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
require_once(ROOT_DIR . "/helpers/lang.php");

$auth = new uAuth();
$langStrings = (new uLang(uConfig::$lang))->getStrings();

$result = [];
$resultAuth = [
  "isAdmin" => $auth->isAdmin(),
  "isAuthenticated" => $auth->isAuthenticated()
];
if ($auth->isAuthenticated()) {
  $resultAuth["userId"] = $auth->user->id;
  $resultAuth["userLogin"] = $auth->user->login;
}

$resultConfig = [
  "interval" => uConfig::$interval,
  "units" => uConfig::$units,
  "lang" => uConfig::$lang,
  "mapApi" => uConfig::$mapapi,
  "gkey" => uConfig::$gkey,
  "initLatitude" => uConfig::$init_latitude,
  "initLongitude" => uConfig::$init_longitude,
  "passRegex" => uConfig::passRegex(),
  "strokeWeight" => uConfig::$strokeWeight,
  "strokeColor" => uConfig::$strokeColor,
  "strokeOpacity" => uConfig::$strokeOpacity,
  "olLayers" => []
];
foreach (uConfig::$ol_layers as $key => $val) {
  $resultConfig["olLayers"][$key] = $val;
}

$resultLang = [];
foreach ($langStrings as $key => $val) {
  $resultLang[$key] = $val;
}

$result["auth"] = $resultAuth;
$result["config"] = $resultConfig;
$result["lang"] = $resultLang;

header("Content-type: application/json");
echo json_encode($result);

?>
