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
$config = uConfig::getInstance();
$langStrings = (new uLang($config))->getStrings();

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
  "interval" => $config->interval,
  "units" => $config->units,
  "lang" => $config->lang,
  "mapApi" => $config->mapApi,
  "gkey" => $config->googleKey,
  "initLatitude" => $config->initLatitude,
  "initLongitude" => $config->initLongitude,
  "passRegex" => $config->passRegex(),
  "strokeWeight" => $config->strokeWeight,
  "strokeColor" => $config->strokeColor,
  "strokeOpacity" => $config->strokeOpacity,
  "olLayers" => []
];
foreach ($config->olLayers as $key => $val) {
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
