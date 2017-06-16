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

define("headless", true);
require_once(dirname(__DIR__) . "/auth.php"); // sets $user
require_once(ROOT_DIR . "/helpers/position.php");
require_once(ROOT_DIR . "/helpers/utils.php");

$userId = (isset($_REQUEST["userid"]) && is_numeric($_REQUEST["userid"])) ? (int) $_REQUEST["userid"] : NULL;
$trackId = (isset($_REQUEST["trackid"]) && is_numeric($_REQUEST["trackid"])) ? (int) $_REQUEST["trackid"] : NULL;

if ($userId) {
  $positionsArr = [];

  if (uConfig::$public_tracks || $user->isAdmin || $user->id === $userId) {
    $position = new uPosition();
    if ($trackId) {
      // get all track data
      $positionsArr = $position->getAll($userId, $trackId);
    } else {
      // get data only for latest point
      if ($position->getLast($userId)->isValid) {
        $positionsArr[] = $position;
      }
    }
  }

  header("Content-type: text/xml");
  $xml = new XMLWriter();
  $xml->openURI("php://output");
  $xml->startDocument("1.0");
  $xml->setIndent(true);
  $xml->startElement('root');

  foreach ($positionsArr as $position) {
    $xml->startElement("position");
    $xml->writeAttribute("id", $position->id);
      $xml->writeElement("latitude", $position->latitude);
      $xml->writeElement("longitude", $position->longitude);
      $xml->writeElement("altitude", ($position->altitude) ? round($position->altitude) : $position->altitude);
      $xml->writeElement("speed", $position->speed);
      $xml->writeElement("bearing", $position->bearing);
      $xml->writeElement("timestamp", $position->timestamp);
      $xml->writeElement("accuracy", $position->accuracy);
      $xml->writeElement("provider", $position->provider);
      $xml->writeElement("comments", $position->comment);
      $xml->writeElement("username", $position->userLogin);
      $xml->writeElement("trackid", $position->trackId);
      $xml->writeElement("trackname", $position->trackName);
      $distance = isset($prevPosition) ? $position->distanceTo($prevPosition) : 0;
      $xml->writeElement("distance", round($distance));
      $seconds = isset($prevPosition) ? $position->secondsTo($prevPosition) : 0;
      $xml->writeElement("seconds", $seconds);
    $xml->endElement();
    $prevPosition = $position;
  }

  $xml->endElement();
  $xml->endDocument();
  $xml->flush();
}

?>
