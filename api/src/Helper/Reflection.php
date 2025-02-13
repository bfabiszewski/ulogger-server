<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Helper;

use Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;

/**
 * Various util functions
 */
class Reflection {


  /**
   * @param mixed $value
   * @param ReflectionNamedType $type
   * @return mixed
   * @throws ServerException
   * @throws InvalidInputException
   */
  public static function castArgument(mixed $value, ReflectionNamedType $type): mixed {
    if (is_null($value)) {
      if ($type->allowsNull()) {
        return null;
      }
      throw new ServerException('Unexpected null value in route parameter');
    }
    $typeName = $type->getName();
    switch ($typeName) {
      case 'int':
        if (!is_numeric($value)) {
          throw new InvalidInputException('Input cannot be cast to an integer');
        }
        return (int) $value;
      case 'float':
        if (!is_numeric($value)) {
          throw new InvalidInputException('Input cannot be cast to a float');
        }
        return (float) $value;
      case 'bool':
        if (!in_array($value, [ '0', '1', 0, 1, false, true ], true)) {
          throw new InvalidInputException('Input cannot be cast to a boolean');
        }
        return (bool) $value;
      default:
        return $value;
    }
  }

  /**
   * @param object|string $objectOrClass
   * @param string $attributeName
   * @return Generator
   * @throws ServerException
   */
  public static function propertyGenerator(object|string $objectOrClass, string $attributeName): Generator {
    try {
      $reflectionClass = new ReflectionClass($objectOrClass);
      foreach ($reflectionClass->getProperties() as $property) {
        $attributes = $property->getAttributes($attributeName);
        foreach ($attributes as $attribute) {
          $attributeInstance = $attribute->newInstance();
          $fieldName = $attributeInstance->getName() ?? $property->getName();
          yield $fieldName => $property;
        }

      }
    } catch (ReflectionException $e) {
      throw new ServerException($e->getMessage());
    }


  }

  /**
   * @throws ServerException
   */
  public static function methodGenerator(object|string $objectOrClass, string $attributeName): Generator {
    try {
      $reflectionClass = new ReflectionClass($objectOrClass);

      foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $attributes = $method->getAttributes($attributeName);
        foreach ($attributes as $attribute) {
          yield $attribute->newInstance() => $method;
        }
      }
    } catch (ReflectionException $e) {
      throw new ServerException($e->getMessage());
    }
  }


}

?>
