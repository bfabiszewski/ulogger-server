#!/usr/bin/env php
<?php
/* μlogger CLI import
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

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Operand;

require_once(dirname(__DIR__) . "/helpers/auth.php");
require_once(ROOT_DIR . "/helpers/track.php");
require_once(ROOT_DIR . "/helpers/position.php");
require_once(ROOT_DIR . "/helpers/utils.php");
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");
if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
  require_once(ROOT_DIR . '/vendor/autoload.php');
}

// check we are running in CLI mode
if (PHP_SAPI !== 'cli') {
  exit('Call me on CLI only!' . PHP_EOL);
}

if (!class_exists("GetOpt\GetOpt")) {
  exit('This script needs ulrichsg/getopt-php package. Please install dependencies via Composer.' . PHP_EOL);
}

// set up argument parsing
$getopt = new GetOpt();
$getopt->addOptions([
  Option::create('h', 'help')
    ->setDescription('Show usage/help'),

  Option::create('u', 'user-id', GetOpt::OPTIONAL_ARGUMENT)
    ->setDescription('Which user to import the track(s) for (default: 1)')
    ->setDefaultValue(1)
    ->setValidation('is_numeric', '%s has to be an integer'),

  Option::create('e', 'import-existing-track')
    ->setDescription('Import already existing tracks (based on track name)'),

  Option::create('l', 'skip-last-track')
    ->setDescription('Skip the last track (for special use cases)'),
]);

$getopt->addOperand(
  Operand::create('gpx', Operand::MULTIPLE + Operand::REQUIRED)
    ->setDescription('One or more GPX files to import')
    ->setValidation('is_readable', '%s: %s is not readable')
);

// process arguments and catch user errors
try {
  $getopt->process();
} catch (Exception $exception) {
  // be nice if the user just asked for help
  if (!$getopt->getOption('help')) {
    exit('ERROR: ' . $exception->getMessage() . PHP_EOL);
  }
}

// show help and quit
if ($getopt->getOption('help')) {
  exit($getopt->getHelpText());
}

// get all tracks for user id
$userId = $getopt->getOption('user-id');
if (!$getopt->getOption('import-existing-track')) {
  $tracksArr = uTrack::getAll($userId);
}

// lets import some GPX tracks!
$gpxFiles = $getopt->getOperand('gpx');
foreach ($gpxFiles as $i => $gpxFile) {
  // skip last track?
  if ($getopt->getOption('skip-last-track') && $i === count($gpxFiles) - 1) {
    continue;
  }

  $gpxName = basename($gpxFile);

  if (!$getopt->getOption('import-existing-track')) {
    foreach ($tracksArr as $track) {
      if ($track->name === $gpxName) {
        print('WARNING: ' . $gpxName . ' already present, skipping...' . PHP_EOL);
        continue 2;
      }
    }
  }

  print('importing ' . $gpxFile.'...' . PHP_EOL);

  $config = uConfig::getInstance();
  $lang = (new uLang($config))->getStrings();

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
  else if ($gpx->getName() !== "gpx") {
    uUtils::exitWithError($lang["iparsefailure"]);
  }
  else if (empty($gpx->trk)) {
    uUtils::exitWithError($lang["idatafailure"]);
  }

  $trackCnt = 0;
  foreach ($gpx->trk as $trk) {
    $trackName = empty($trk->name) ? $gpxName : (string) $trk->name;
    $metaName = empty($gpx->metadata->name) ? NULL : (string) $gpx->metadata->name;
    $trackId = uTrack::add($userId, $trackName, $metaName);
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
        $time = isset($point->time) ? strtotime($point->time) : 0;
        $altitude = isset($point->ele) ? (double) $point->ele : NULL;
        $speed = NULL;
        $bearing = NULL;
        $accuracy = NULL;
        $provider = "gps";
        if (!empty($point->extensions)) {
          // parse ulogger extensions
          $ext = $point->extensions->children('ulogger', true);
          if (count($ext->speed)) { $speed = (double) $ext->speed; }
          if (count($ext->bearing)) { $bearing = (double) $ext->bearing; }
          if (count($ext->accuracy)) { $accuracy = (int) $ext->accuracy; }
          if (count($ext->provider)) { $provider = (string) $ext->provider; }
        }
        $ret = $track->addPosition($userId,
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
}
?>
