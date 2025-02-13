<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper\Fixtures;

class Positions {
  /** @var string Table name */
  public string $table = 'positions';

  /** @var array Records */
  public array $records = [
    [
      'id' => 1,
      'time' => '2019-06-10 12:51:39',
      'user_id' => 1,
      'track_id' => 1,
      'latitude' => 52.222,
      'longitude' => 21.0,
      'altitude' => 131,
      'speed' => 4.6,
      'bearing' => 136.3,
      'accuracy' => 4,
      'provider' => 'gps',
      'comment' => 'comment 1',
      'image' => '1_62480e2ae1ba4.jpg',
    ],
    [
      'id' => 2,
      'time' => '2019-06-10 12:52:12',
      'user_id' => 1,
      'track_id' => 1,
      'latitude' => 52.224,
      'longitude' => 21.1,
      'altitude' => 130,
      'speed' => 0.6,
      'bearing' => null,
      'accuracy' => 12,
      'provider' => 'gps',
      'comment' => null,
      'image' => null,
    ],
    [
      'id' => 3,
      'time' => '2019-07-01 05:02:54',
      'user_id' => 2,
      'track_id' => 2,
      'latitude' => 0.1,
      'longitude' => 12.34,
      'altitude' => 10,
      'speed' => null,
      'bearing' => null,
      'accuracy' => 12,
      'provider' => 'network',
      'comment' => null,
      'image' => null,
    ],
    [
      'id' => 4,
      'time' => '2020-01-01 02:09:04',//1577844544
      'user_id' => 1,
      'track_id' => 3,
      'latitude' => 15.65,
      'longitude' => 11.34,
      'altitude' => 555,
      'speed' => 5.5,
      'bearing' => 34.4,
      'accuracy' => 1,
      'provider' => 'network',
      'comment' => null,
      'image' => null,
    ]
  ];
}
