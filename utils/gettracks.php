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
require_once(ROOT_DIR . "/helpers/track.php");

$userId = (isset($_REQUEST["userid"]) && is_numeric($_REQUEST["userid"])) ? (int) $_REQUEST["userid"] : NULL;

if ($userId) {
  $tracksArr = [];

  if (uConfig::$public_tracks || $user->isAdmin || $user->id === $userId) {
    $track = new uTrack();
    $tracksArr = $track->getAll($userId);
  }

  header("Content-type: text/xml");
  $xml = new XMLWriter();
  $xml->openURI("php://output");
  $xml->startDocument("1.0");
  $xml->setIndent(true);
  $xml->startElement('root');

  if (!empty($tracksArr)) {
    foreach ($tracksArr as $aTrack) {
      $xml->startElement("track");
        $xml->writeElement("trackid", $aTrack->id);
        $xml->writeElement("trackname", $aTrack->name);
      $xml->endElement();
    }
  }

  $xml->endElement();
  $xml->endDocument();
  $xml->flush();
}

?>
