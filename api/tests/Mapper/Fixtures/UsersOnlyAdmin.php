<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Mapper\Fixtures;

class UsersOnlyAdmin {
  /** @var string Table name */
  public string $table = 'users';

  /** @var array Records */
  public array $records = [
    [
      'id'=> '1',
      'login'=> 'admin',
      'password'=> '$2y$10$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq',
      'admin'=> '1'
    ]
  ];
}
