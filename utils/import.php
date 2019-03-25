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
require_once(ROOT_DIR . "/helpers/track.php");
require_once(ROOT_DIR . "/helpers/position.php");
require_once(ROOT_DIR . "/helpers/utils.php");
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");

$auth = new uAuth();

$lang = (new uLang(uConfig::$lang))->getStrings();

$uploadErrors = [];
$uploadErrors[UPLOAD_ERR_INI_SIZE] = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
$uploadErrors[UPLOAD_ERR_FORM_SIZE] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
$uploadErrors[UPLOAD_ERR_PARTIAL] = "The uploaded file was only partially uploaded";
$uploadErrors[UPLOAD_ERR_NO_FILE] = "No file was uploaded";
$uploadErrors[UPLOAD_ERR_NO_TMP_DIR] = "Missing a temporary folder";
$uploadErrors[UPLOAD_ERR_CANT_WRITE] = "Failed to write file to disk";
$uploadErrors[UPLOAD_ERR_EXTENSION] = "A PHP extension stopped the file upload";

if (!$auth->isAuthenticated()) {
  uUtils::exitWithError($lang["private"]);
}

if (!isset($_FILES["gpx"])) {
  $message = $lang["servererror"];
  $lastErr = error_get_last();
  if (!empty($lastErr)) {
    $message .= ": " . $lastErr["message"];
  } else {
    $message .= ": no uploaded file";
  }
  uUtils::exitWithError($message);
}

$gpxFile = NULL;
$gpxUpload = $_FILES["gpx"];
$uploadErr = $gpxUpload["error"];
if ($gpxUpload["size"] > uUtils::getUploadMaxSize() && $uploadErr == UPLOAD_ERR_OK) {
  $uploadErr = UPLOAD_ERR_FORM_SIZE;
}
if ($uploadErr == UPLOAD_ERR_OK) {
  $gpxFile = $gpxUpload["tmp_name"];
  $gpxName = basename($gpxUpload["name"]);
} else {
  $message = $lang["iuploadfailure"];
  if (isset($uploadErrors[$uploadErr])) {
    $message .= ": " . $uploadErrors[$uploadErr];
  }
  $message .= " ($uploadErr)";
  uUtils::exitWithError($message);
}

$gpx = false;
libxml_use_internal_errors(true);
if ($gpxFile && file_exists($gpxFile)) {
  $gpx = simplexml_load_file($gpxFile);
}

if ($gpx === false) {
  $message = $lang["iparsefailure"];
  $parserMessages = [];
  foreach(libxml_get_errors() as $parseError) {
    $parserMessages[] = $parseError->message;
  }
  $parserMessage = implode(", ", $parserMessages);
  if (!empty($parserMessage)) {
    $message .= ": $parserMessage";
  }
  uUtils::exitWithError($message);
}
else if ($gpx->getName() != "gpx") {
    uUtils::exitWithError($lang["iparsefailure"]);
}
else if (empty($gpx->trk)) {
  uUtils::exitWithError($lang["idatafailure"]);
}

$trackCnt = 0;
foreach ($gpx->trk as $trk) {
  $trackName = empty($trk->name) ? $gpxName : (string) $trk->name;
  $metaName = empty($gpx->metadata->name) ? NULL : (string) $gpx->metadata->name;
  $trackId = uTrack::add($auth->user->id, $trackName, $metaName);
  if ($trackId === false) {
    uUtils::exitWithError($lang["servererror"]);
    break;
  }
  $track = new uTrack($trackId);
  $posCnt = 0;

  foreach($trk->trkseg as $segment) {
    foreach($segment->trkpt as $point) {
      if (!isset($point["lat"]) || !isset($point["lon"])) {
        $track->delete();
        uUtils::exitWithError($lang["iparsefailure"]);
      }
      $time = isset($point->time) ? strtotime($point->time) : 1;
      $altitude = isset($point->ele) ? (double) $point->ele : NULL;
      $speed = NULL;
      $bearing = NULL;
      $accuracy = NULL;
      $provider = "gps";
      if (!empty($point->extensions)) {
        // parse ulogger extensions
        $ext = $point->extensions->children('ulogger', TRUE);
        if (count($ext->speed)) { $speed = (double) $ext->speed; }
        if (count($ext->bearing)) { $bearing = (double) $ext->bearing; }
        if (count($ext->accuracy)) { $accuracy = (int) $ext->accuracy; }
        if (count($ext->provider)) { $provider = (string) $ext->provider; }
      }
      $ret = $track->addPosition($auth->user->id,
                    $time, (double) $point["lat"], (double) $point["lon"], $altitude,
                    $speed, $bearing, $accuracy, $provider, NULL, NULL);
      if ($ret === false) {
        $track->delete();
        uUtils::exitWithError($lang["servererror"]);
      }
      $posCnt++;
    }
  }
  if ($posCnt) {
    $trackCnt++;
  } else {
    $track->delete();
  }
}

// return last track id and tracks count
uUtils::exitWithSuccess([ "trackid" => $trackId, "trackcnt" => $trackCnt ]);

?>
