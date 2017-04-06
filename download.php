<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
require_once("auth.php");
$type = (isset($_REQUEST["type"]) ? $_REQUEST["type"] : "kml");
$userid = ((isset($_REQUEST["userid"]) && is_numeric($_REQUEST["userid"])) ? $_REQUEST["userid"] : 0);
$trackid = ((isset($_REQUEST["trackid"]) && is_numeric($_REQUEST["trackid"])) ? $_REQUEST["trackid"] : 0);

if ($config::$units=="imperial") {
  $factor_kmh = 0.62; //to mph
  $unit_kmh = "mph";
  $factor_m = 3.28; // to feet
  $unit_m = "ft";
  $factor_km = 0.62; // to miles
  $unit_km = "mi";
}
else {
  $factor_kmh = 1;
  $unit_kmh = "km/h";
  $factor_m = 1;
  $unit_m = "m";
  $factor_km = 1;
  $unit_km = "km";
}

function haversine_distance($lat1, $lon1, $lat2, $lon2) {
  $lat1 = deg2rad($lat1);
  $lon1 = deg2rad($lon1);
  $lat2 = deg2rad($lat2);
  $lon2 = deg2rad($lon2);
  $latD = $lat2 - $lat1;
  $lonD = $lon2 - $lon1;
  $bearing = 2*asin(sqrt(pow(sin($latD/2),2)+cos($lat1)*cos($lat2)*pow(sin($lonD/2),2)));
  return $bearing * 6371000;
}
function addStyle($xml,$name,$url) {
  $xml->startElement("Style");
  $xml->writeAttribute("id", $name."Style");
    $xml->startElement("IconStyle");
    $xml->writeAttribute("id", $name."Icon");
      $xml->startElement("Icon");
        $xml->writeElement("href", $url);
      $xml->endElement();
    $xml->endElement();
  $xml->endElement();
}
function toHMS($s) {
  $d = floor($s/86400);
  $h = floor(($s%86400)/3600);
  $m = floor((($s%86400)%3600)/60);
  $s = (($s%86400)%3600)%60;
  return (($d>0)?($d." d "):"").(substr("00".$h,-2)).":".(substr("00".$m,-2)).":".(substr("00".$s,-2));
}

if ($trackid>0 && $userid>0) {
  $query = $mysqli->prepare("SELECT p.id, p.latitude, p.longitude, p.altitude, p.speed, p.bearing, p.time, u.login, t.name 
                             FROM positions p
                             LEFT JOIN users u ON (p.user_id=u.id) 
                             LEFT JOIN tracks t ON (p.track_id=t.id) 
                             WHERE p.user_id=? AND p.track_id=? 
                             ORDER BY p.time");
  $query->bind_param("ii", $userid, $trackid);
  $query->execute();
  $query->store_result();
  $query->bind_result($positionid,$latitude,$longitude,$altitude,$speed,$bearing,$dateoccured,$username,$trackname);
  $query->fetch(); // take just one row to get trackname etc
  $query->data_seek(0); // and reset result set
  switch ($type) {
    case "kml":
    default:
      header("Content-type: application/vnd.google-earth.kml+xml");
      header("Content-Disposition: attachment; filename=\"track$trackid.kml\"");
      $xml = new XMLWriter();
      $xml->openURI("php://output");
      $xml->startDocument("1.0");
      $xml->startElement("kml");
      $xml->writeAttribute("xmlns", "http://earth.google.com/kml/2.1");
      $xml->setIndent(true);
      $xml->startElement("Document");
      $xml->writeElement("name", $trackname);
      // line style
      $xml->startElement("Style");
        $xml->writeAttribute("id", "lineStyle");
        $xml->startElement("LineStyle");
          $xml->writeElement("color","7f0000ff");
          $xml->writeElement("width","4");
        $xml->endElement();
      $xml->endElement();
      // marker styles
      addStyle($xml,"red","http://maps.google.com/mapfiles/markerA.png");
      addStyle($xml,"green","http://maps.google.com/mapfiles/marker_greenB.png");
      addStyle($xml,"gray","http://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_gray.png");
      $style = "#redStyle"; // for first element
      $i = 0;
      $totalMeters = 0;
      $totalSeconds = 0;
      while ($query->fetch()) {
        $distance = (isset($prev_latitude))?haversine_distance($prev_latitude,$prev_longitude,$latitude,$longitude):0;
        $prev_latitude = $latitude;
        $prev_longitude = $longitude;
        $seconds = (isset($prev_dateoccured))?(strtotime($dateoccured)-strtotime($prev_dateoccured)):0;
        $prev_dateoccured = $dateoccured;
        $totalMeters += $distance;
        $totalSeconds += $seconds;

        if(++$i == $query->num_rows) { $style = "#greenStyle"; } // last element
        $xml->startElement("Placemark");
        $xml->writeAttribute("id", $positionid);
          //$xml->writeElement("name", $i);
          $description =
          "<div style=\"font-weight: bolder;padding-bottom: 10px;border-bottom: 1px solid gray;\">".$lang_user.": ".strtoupper($username)."<br />".$lang_track.": ".strtoupper($trackname).
          "</div>".
          "<div>".
          "<div style=\"padding-top: 10px;\"><b>".$lang_time.":</b> ".$dateoccured."<br />".
          (($speed)?"<b>".$lang_speed.":</b> ".round($speed*3.6,2*$factor_kmh)." ".$unit_kmh."<br />":"").
          (($altitude != null)?"<b>".$lang_altitude.":</b> ".round($altitude*$factor_m)." ".$unit_m."<br />":"").
          "<b>".$lang_ttime.":</b> ".toHMS($totalSeconds)."<br />".
          "<b>".$lang_aspeed.":</b> ".(($totalSeconds!=0)?round($totalMeters/$totalSeconds*3.6*$factor_kmh,2):0)." ".$unit_kmh."<br />".
          "<b>".$lang_tdistance.":</b> ".round($totalMeters/1000*$factor_km,2)." ".$unit_km."<br />"."</div>".
          "<div style=\"font-size: smaller;padding-top: 10px;\">".$lang_point." ".$i." ".$lang_of." ".($query->num_rows-1)."</div>".
          "</div>";
          $xml->startElement("description");
            $xml->writeCData($description);
          $xml->endElement();
          $xml->writeElement("styleUrl", $style);
          $xml->startElement("Point");
            $coordinate[$i] = $longitude.",".$latitude.(($altitude) ? ",".$altitude : "");
            $xml->writeElement("coordinates", $coordinate[$i]);
          $xml->endElement();
        $xml->endElement();
        $style = "#grayStyle"; // other elements
      }
      $coordinates = implode("\n",$coordinate);
      $xml->startElement("Placemark");
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
      header("Content-Disposition: attachment; filename=\"track$trackid.gpx\"");
      $xml = new XMLWriter();
      $xml->openURI("php://output");
      $xml->startDocument("1.0");
      $xml->startElement("gpx");
      $xml->writeAttribute("xmlns", "http://www.topografix.com/GPX/1/1");
      $xml->writeAttribute("xmlns:gpxdata", "http://www.cluetrust.com/XML/GPXDATA/1/0");
      $xml->writeAttribute("creator", "μlogger");
      $xml->writeAttribute("version", "1.1");
      $xml->startElement("metadata");
        $xml->writeElement("name", $trackname);
        $xml->writeElement("time", str_replace(" ","T",$dateoccured));
      $xml->endElement();
      $xml->startElement("trk");
        $xml->writeElement("name", $trackname);
        $xml->startElement("trkseg");
        $i = 0;
        $totalMeters = 0;
        $totalSeconds = 0;
        while ($query->fetch()) {
          $distance = (isset($prev_latitude))?haversine_distance($prev_latitude,$prev_longitude,$latitude,$longitude):0;
          $prev_latitude = $latitude;
          $prev_longitude = $longitude;
          $seconds = (isset($prev_dateoccured))?(strtotime($dateoccured)-strtotime($prev_dateoccured)):0;
          $prev_dateoccured = $dateoccured;
          $totalMeters += $distance;
          $totalSeconds += $seconds;
          $xml->startElement("trkpt");
            $xml->writeAttribute("lat", $latitude);
            $xml->writeAttribute("lon", $longitude);
            if($altitude) { $xml->writeElement("ele", $altitude); }
            $xml->writeElement("time", str_replace(" ","T",$dateoccured));
            $xml->writeElement("name", ++$i);
            $xml->startElement("desc");
              $description =
              $lang_user.": ".strtoupper($username)." ".$lang_track.": ".strtoupper($trackname).
              " ".$lang_time.": ".$dateoccured.
              (($speed)?" ".$lang_speed.": ".round($speed*3.6,2*$factor_kmh)." ".$unit_kmh:"").
              (($altitude != null)?" ".$lang_altitude.": ".round($altitude*$factor_m)." ".$unit_m:"").
              " ".$lang_ttime.": ".toHMS($totalSeconds)."".
              " ".$lang_aspeed.": ".(($totalSeconds!=0)?round($totalMeters/$totalSeconds*3.6*$factor_kmh,2):0)." ".$unit_kmh.
              " ".$lang_tdistance.": ".round($totalMeters/1000*$factor_km,2)." ".$unit_km.
              " ".$lang_point." ".$i." ".$lang_of." ".($query->num_rows-1);
              $xml->writeCData($description);
            $xml->endElement();
          $xml->endElement();
        }
        $xml->endElement();
      $xml->endElement();
      $xml->endElement();
      $xml->endDocument();
      $xml->flush();

      break;
  }
  $query->free_result();
  $query->close();
}
$mysqli->close();
?>
