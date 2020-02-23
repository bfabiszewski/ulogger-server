<?php
/**
 * μlogger
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
require_once(ROOT_DIR . "/helpers/config.php");

$auth = new uAuth();
if (!$auth->isAdmin()) {
  uUtils::exitWithError($lang["notauthorized"]);
}

$olLayers = uUtils::postArray('olLayers');

$data = [
  'map_api' => uUtils::postString('mapApi'),
  'latitude' => uUtils::postFloat('initLatitude'),
  'longitude' => uUtils::postFloat('initLongitude'),
  'google_key' => uUtils::postString('googleKey'),
  'require_auth' => uUtils::postBool('requireAuth'),
  'public_tracks' => uUtils::postBool('publicTracks'),
  'pass_lenmin' => uUtils::postInt('passLenMin'),
  'pass_strength' => uUtils::postInt('passStrength'),
  'interval_seconds' => uUtils::postInt('interval'),
  'lang' => uUtils::postString('lang'),
  'units' => uUtils::postString('units'),
  'stroke_weight' => uUtils::postInt('strokeWeight'),
  'stroke_color' => uUtils::postString('strokeColor'),
  'stroke_opacity' => uUtils::postFloat('strokeOpacity'),
  'color_normal' => uUtils::postInt('colorNormal'),
  'color_start' => uUtils::postInt('colorStart'),
  'color_stop' => uUtils::postInt('colorStop'),
  'color_extra' => uUtils::postInt('colorExtra'),
  'color_hilite' => uUtils::postInt('colorHilite')
];

$config = uConfig::getInstance();
$config->setFromArray($data);
if (!is_null($olLayers)) {
  $config->olLayers = [];
  foreach ($olLayers as $json) {
    $obj = json_decode($json);
    if (json_last_error() === JSON_ERROR_NONE) {
      $config->olLayers[] = new uLayer($obj->id, $obj->name, $obj->url, $obj->priority);
    }
  }
}

if ($config->save() === false) {
  uUtils::exitWithError("Server error");
}
uUtils::exitWithSuccess();
