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
require_once(ROOT_DIR . "/helpers/lang.php");
require_once(ROOT_DIR . "/helpers/config.php");

$auth = new uAuth();
$config = uConfig::getInstance();
$lang = (new uLang($config))->getStrings();

/**
 * Add kml marker style element
 *
 * @param XMLWriter $xml Writer object
 * @param string $name Color name
 * @param string $url Url
 */
function addStyle($xml, $name, $url) {
  $xml->startElement("Style");
  $xml->writeAttribute("id", "{$name}Style");
    $xml->startElement("IconStyle");
    $xml->writeAttribute("id", "{$name}Icon");
      $xml->startElement("Icon");
        $xml->writeElement("href", $url);
      $xml->endElement();
    $xml->endElement();
  $xml->endElement();
}

/**
 * Convert seconds to [day], hour, minute, second string
 *
 * @param int $s Number of seconds
 * @return string [d ]hhmmss
 */
function toHMS($s) {
  $d = floor($s / 86400);
  $h = floor(($s % 86400) / 3600);
  $m = floor((($s % 86400) % 3600) / 60);
  $s = (($s % 86400) % 3600) % 60;
  return (($d > 0) ? "$d d " : "") . sprintf("%02d:%02d:%02d", $h, $m, $s);
}

$type = uUtils::getString('type', 'kml');
$userId = uUtils::getInt('userid');
$trackId = uUtils::getInt('trackid');

if (!$config->publicTracks &&
    (!$auth->isAuthenticated() || (!$auth->isAdmin() && $auth->user->id !== $userId))) {
  // unauthorized
  exit();
}

if ($config->units === "imperial") {
  $factor_kmh = 0.62; //to mph
  $unit_kmh = "mph";
  $factor_m = 3.28; // to feet
  $unit_m = "ft";
  $factor_km = 0.62; // to miles
  $unit_km = "mi";
} else {
  $factor_kmh = 1;
  $unit_kmh = "km/h";
  $factor_m = 1;
  $unit_m = "m";
  $factor_km = 1;
  $unit_km = "km";
}

if ($trackId && $userId) {
  $positionsArr = uPosition::getAll($userId, $trackId);
  if (empty($positionsArr)) {
    exit();
  }

  switch ($type) {
    case "kml":
    default:
      header("Content-type: application/vnd.google-earth.kml+xml");
      header("Content-Disposition: attachment; filename=\"track{$positionsArr[0]->trackId}.kml\"");
      $xml = new XMLWriter();
      $xml->openURI("php://output");
      $xml->setIndent(true);
      $xml->startDocument("1.0", "utf-8");
      $xml->startElement("kml");
      $xml->writeAttributeNs("xsi", "schemaLocation", NULL, "http://www.opengis.net/kml/2.2 http://schemas.opengis.net/kml/2.2.0/ogckml22.xsd");
      $xml->writeAttributeNs("xmlns", "xsi", NULL, "http://www.w3.org/2001/XMLSchema-instance");
      $xml->writeAttribute("xmlns", "http://www.opengis.net/kml/2.2");
      $xml->startElement("Document");
      $xml->writeElement("name", $positionsArr[0]->trackName);
      // line style
      $xml->startElement("Style");
        $xml->writeAttribute("id", "lineStyle");
        $xml->startElement("LineStyle");
          $xml->writeElement("color", "7f0000ff");
          $xml->writeElement("width", "4");
        $xml->endElement();
      $xml->endElement();
      // marker styles
      addStyle($xml, "red", "http://maps.google.com/mapfiles/markerA.png");
      addStyle($xml, "green", "http://maps.google.com/mapfiles/marker_greenB.png");
      addStyle($xml, "gray", "http://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_gray.png");
      $style = "#redStyle"; // for first element
      $i = 0;
      $totalMeters = 0;
      $totalSeconds = 0;
      $coordinate = [];
      foreach ($positionsArr as $position) {
        $distance = isset($prevPosition) ? $position->distanceTo($prevPosition) : 0;
        $seconds = isset($prevPosition) ? $position->secondsTo($prevPosition) : 0;
        $prevPosition = $position;
        $totalMeters += $distance;
        $totalSeconds += $seconds;

        if(++$i === count($positionsArr)) { $style = "#greenStyle"; } // last element
        $xml->startElement("Placemark");
        $xml->writeAttribute("id", "point_{$position->id}");
          $description =
          "<div style=\"font-weight: bolder; padding-bottom: 10px; border-bottom: 1px solid gray;\">" .
          "{$lang["user"]}: " . htmlspecialchars($position->userLogin) . "<br>{$lang["track"]}: " . htmlspecialchars($position->trackName) .
          "</div>" .
          "<div>" .
          "<div style=\"padding-top: 10px;\"><b>{$lang["time"]}:</b> " . date("Y-m-d H:i:s (e)", $position->timestamp) . "<br>" .
          (!is_null($position->comment) ? "<b>{$position->comment}</b><br>" : "") .
          (!is_null($position->speed) ? "<b>{$lang["speed"]}:</b> " . round($position->speed * 3.6 * $factor_kmh, 2) . " {$unit_kmh}<br>" : "") .
          (!is_null($position->altitude) ? "<b>{$lang["altitude"]}:</b> " . round($position->altitude * $factor_m) . " {$unit_m}<br>" : "") .
          "<b>{$lang["ttime"]}:</b> " . toHMS($totalSeconds) . "<br>" .
          "<b>{$lang["aspeed"]}:</b> " . (($totalSeconds !== 0) ? round($totalMeters / $totalSeconds * 3.6 * $factor_kmh, 2) : 0) . " {$unit_kmh}<br>" .
          "<b>{$lang["tdistance"]}:</b> " . round($totalMeters / 1000 * $factor_km, 2) . " " . $unit_km . "<br></div>" .
          "<div style=\"font-size: smaller; padding-top: 10px;\">" . sprintf($lang["pointof"], $i, count($positionsArr)) . "</div>" .
          "</div>";
          $xml->startElement("description");
            $xml->writeCData($description);
          $xml->endElement();
          $xml->writeElement("styleUrl", $style);
          $xml->startElement("Point");
            $coordinate[$i] = "{$position->longitude},{$position->latitude}" . (!is_null($position->altitude) ? ",{$position->altitude}" : "");
            $xml->writeElement("coordinates", $coordinate[$i]);
          $xml->endElement();
        $xml->endElement();
        $style = "#grayStyle"; // other elements
      }
      $coordinates = implode("\n", $coordinate);
      $xml->startElement("Placemark");
      $xml->writeAttribute("id", "lineString");
        $xml->writeElement("styleUrl", "#lineStyle");
        $xml->startElement("LineString");
          $xml->writeElement("coordinates", $coordinates);
        $xml->endElement();
      $xml->endElement();

      $xml->endElement();
      $xml->endElement();
      $xml->endDocument();
      $xml->flush();

      break;

    case "gpx":
      header("Content-type: application/application/gpx+xm");
      header("Content-Disposition: attachment; filename=\"track" . $positionsArr[0]->trackId . ".gpx\"");
      $xml = new XMLWriter();
      $xml->openURI("php://output");
      $xml->setIndent(true);
      $xml->startDocument("1.0", "utf-8");
      $xml->startElement("gpx");
      $xml->writeAttribute("xmlns", "http://www.topografix.com/GPX/1/1");
      $xml->writeAttributeNs("xsi", "schemaLocation", NULL, "http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd https://github.com/bfabiszewski/ulogger-android/1 https://raw.githubusercontent.com/bfabiszewski/ulogger-server/master/scripts/gpx_extensions1.xsd");
      $xml->writeAttributeNs("xmlns", "xsi", NULL, "http://www.w3.org/2001/XMLSchema-instance");
      $xml->writeAttributeNs("xmlns", "ulogger", NULL, "https://github.com/bfabiszewski/ulogger-android/1");
      $xml->writeAttribute("creator", "μlogger-server " . $config->version);
      $xml->writeAttribute("version", "1.1");
      $xml->startElement("metadata");
        $xml->writeElement("name", $positionsArr[0]->trackName);
        $xml->writeElement("time", gmdate("Y-m-d\TH:i:s\Z", $positionsArr[0]->timestamp));
      $xml->endElement();
      $xml->startElement("trk");
        $xml->writeElement("name", $positionsArr[0]->trackName);
        $xml->startElement("trkseg");
        $i = 0;
        $totalMeters = 0;
        $totalSeconds = 0;
        foreach ($positionsArr as $position) {
          $distance = isset($prevPosition) ? $position->distanceTo($prevPosition) : 0;
          $seconds = isset($prevPosition) ? $position->secondsTo($prevPosition) : 0;
          $prevPosition = $position;
          $totalMeters += $distance;
          $totalSeconds += $seconds;
          $xml->startElement("trkpt");
            $xml->writeAttribute("lat", $position->latitude);
            $xml->writeAttribute("lon", $position->longitude);
            if (!is_null($position->altitude)) { $xml->writeElement("ele", $position->altitude); }
            $xml->writeElement("time", gmdate("Y-m-d\TH:i:s\Z", $position->timestamp));
            $xml->writeElement("name", ++$i);
            if (!is_null($position->comment)) {
              $xml->startElement("desc");
              $xml->writeCData($position->comment);
              $xml->endElement();
            }
            if (!is_null($position->speed) || !is_null($position->bearing) || !is_null($position->accuracy) || !is_null($position->provider)) {
              $xml->startElement("extensions");

              if (!is_null($position->speed)) {
                $xml->writeElementNS("ulogger", "speed", NULL, $position->speed);
              }
              if (!is_null($position->bearing)) {
                $xml->writeElementNS("ulogger", "bearing", NULL, $position->bearing);
              }
              if (!is_null($position->accuracy)) {
                $xml->writeElementNS("ulogger", "accuracy", NULL, $position->accuracy);
              }
              if (!is_null($position->provider)) {
                $xml->writeElementNS("ulogger", "provider", NULL, $position->provider);
              }

              $xml->endElement();
            }
          $xml->endElement();
        }
        $xml->endElement();
      $xml->endElement();
      $xml->endElement();
      $xml->endDocument();
      $xml->flush();

      break;
  }

}
?>
