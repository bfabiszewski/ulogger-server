#!/usr/bin/env php
<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

require_once(__DIR__ . '/../vendor/autoload.php');

use GetOpt\{GetOpt, Operand, Option};
use uLogger\Component\Db;
use uLogger\Component\Session;
use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\GpxParseException;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\NotFoundException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Gpx;
use uLogger\Mapper\MapperFactory;
use uLogger\Mapper;

// check we are running in CLI mode
if (PHP_SAPI !== 'cli') {
  print('Call me on CLI only!' . PHP_EOL);
  exit(1);
}

if (!class_exists(GetOpt::class)) {
  print('This script needs ulrichsg/getopt-php package. Please install dependencies via Composer.' . PHP_EOL);
  exit(1);
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
    print('ERROR: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
  }
}

// show help and quit
if ($getopt->getOption('help')) {
  print($getopt->getHelpText());
  exit(0);
}

$exitCode = 1;

// get all tracks for user id
$userId = (int) $getopt->getOption('user-id');

// lets import some GPX tracks!
try {
  $mapperFactory = new MapperFactory(Db::createFromConfig());
  /** @var Mapper\Track $trackMapper */
  $trackMapper = $mapperFactory->getMapper(Mapper\Track::class);
  /** @var Mapper\User $userMapper */
  $userMapper = $mapperFactory->getMapper(Mapper\User::class);
  $config = Entity\Config::createFromMapper($mapperFactory);

  $user = $userMapper->fetch($userId);
  $session = new Session($mapperFactory, $config);
  $session->init();
  $session->setAuthenticatedAndStore($user);

  $gpxFiles = $getopt->getOperand('gpx');
  $importedTracks = [];
  foreach ($gpxFiles as $i => $gpxFile) {
    // skip last track?
    if ($getopt->getOption('skip-last-track') && $i === count($gpxFiles) - 1) {
      continue;
    }

    $gpxName = basename($gpxFile);

    if (!$getopt->getOption('import-existing-track')) {
      $tracksArr = $trackMapper->fetchByUser($userId);
      foreach ($tracksArr as $track) {
        if ($track->name === $gpxName) {
          print('WARNING: ' . $gpxName . ' already present, skipping...' . PHP_EOL);
          continue 2;
        }
      }
    }

    print('importing ' . $gpxFile . '...' . PHP_EOL);

    $gpx = new Gpx($gpxName, $config, $mapperFactory);
    $importedTracks += $gpx->import($session->user->id, $gpxFile);
  }

  print('Success, imported ' . count($importedTracks));
  print(count($importedTracks) === 1 ? ' track:' : ' tracks:');
  print PHP_EOL;
  foreach ($importedTracks as $track) {
    print "  - $track->name" . PHP_EOL;
  }
  $exitCode = 0;

} catch (DatabaseException $e) {
  print("Error, database problem: {$e->getMessage()}" . PHP_EOL);
} catch (InvalidInputException|NotFoundException $e) {
  print("Error, user not found by ID $userId ({$e->getMessage()})" . PHP_EOL);
} catch (GpxParseException $e) {
  print("Error, GPX parsing problem: {$e->getMessage()}" . PHP_EOL);
} catch (ServerException $e) {
  print("Error, server exception: {$e->getMessage()}" . PHP_EOL);
} finally {
  if (isset($session)) {
    $session->sessionEnd();
  }
  exit($exitCode);
}
?>
