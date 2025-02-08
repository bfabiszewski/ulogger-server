<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Entity;

use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use uLogger\Attribute\Column;
use uLogger\Attribute\JsonField;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Reflection;

abstract class AbstractEntity implements JsonSerializable {

  /** @var ReflectionProperty[][] $propertyCache */
  private static array $propertyCache = [];

  /**
   * @throws ServerException
   * @return ReflectionProperty[]
   */
  private static function getPropertyCache(string $attribute): array {
    self::updateCache($attribute);
    return self::$propertyCache[static::class . "::$attribute"];
  }

  /**
   * @throws ServerException
   */
  public function jsonSerialize(): array {

    $jsonData = [];
    foreach (self::getPropertyCache(JsonField::class) as $property) {
      $fieldName = $property->getName();
      $jsonData[$fieldName] = $this->{$fieldName};
    }

    return $jsonData;
  }

  /**
   * @throws ServerException
   * @throws InvalidInputException
   */
  public static function fromPayload(array $payload): static {

    return self::getHydratedInstance($payload, JsonField::class);
  }

  /**
   * @throws ServerException
   */
  public static function fromDatabaseRow(array $row): static {

    try {
      return self::getHydratedInstance($row, Column::class);
    } catch (InvalidInputException $e) {
      throw new ServerException($e->getMessage());
    }
  }

  /**
   * @throws ServerException
   * @throws InvalidInputException
   */
  private static function getHydratedInstance(array $data, string $attribute): static {

    try {
      $reflectionClass = new ReflectionClass(static::class);
      $instance = $reflectionClass->newInstanceWithoutConstructor();
    } catch (ReflectionException $e) {
      throw new ServerException($e->getMessage());
    }

    foreach (self::getPropertyCache($attribute) as $name => $property) {
      if (array_key_exists($name, $data)) {

        $type = $property->getType();
        if (!$type instanceof ReflectionNamedType) {
          throw new ServerException("Parameter '$name' is not named type");
        }
        $property->setValue($instance, Reflection::castArgument($data[$name], $type));
      } elseif ($property->hasDefaultValue()) {
        $property->setValue($instance, $property->getDefaultValue());
      } else {
        throw new InvalidInputException("Missing value for field '{$property->getName()}'");
      }
    }

    return $instance;
  }

  /**
   * @throws ServerException
   */
  private static function updateCache($attribute): void {
    $className = static::class;
    $key = "$className::$attribute";
    if (!isset(self::$propertyCache[$key])) {

      foreach (Reflection::propertyGenerator($className, $attribute) as $field => $property) {
        self::$propertyCache[$key][$field] = $property;
      }
    }
  }




}
