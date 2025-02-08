<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Component;

use JsonException;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use uLogger\Entity\AbstractEntity;
use uLogger\Exception\InvalidInputException;
use uLogger\Exception\ServerException;
use uLogger\Helper\Reflection;
use uLogger\Helper\Utils;

class Request {
  private const TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';
  private const TYPE_MULTIPART_FORM_DATA = 'multipart/form-data';
  private const TYPE_MULTIPART_RELATED = 'multipart/related';
  public const CONTENT_TYPE = 'Content-Type';
  public const CONTENT_LENGTH = 'Content-Length';
  public const CONTENT_DISPOSITION = 'Content-Disposition';

  /** @var string */
  private string $path;
  /** @var string[] */
  private array $uriSegments;
  /** @var string */
  private string $method;
  private string $contentType;
  /** @var array<string, mixed> $params */
  private array $params;

  /** @var array<string, mixed> $payload */
  private array $payload;
  /** @var array<string, FileUpload> $uploads */
  private array $uploads;
  /** @var array */
  private array $filters;
  private array $preparedArguments = [];

  public const METHOD_GET = 'GET';
  public const METHOD_PUT = 'PUT';
  public const METHOD_DELETE = 'DELETE';
  public const METHOD_POST = 'POST';

  /**
   * @param string $path
   * @param string $method
   * @param array $payload
   * @param array $uploads
   * @param array $filters
   */
  public function __construct(string $path = '', string $method = '', array $payload = [], array $uploads = [], array $filters = []) {
    $this->path = $path;
    $this->method = $method;
    $this->filters = $filters;
    $this->payload = $payload;
    $this->uploads = $uploads;
    $this->setUriSegments();
  }

  public function getUriSegments(): array {
    return $this->uriSegments;
  }

  public function getMethod(): string {
    return $this->method;
  }

  public function getParams(): array {
    return $this->params;
  }

  public function getPayload(): array {
    return $this->payload;
  }

  public function getFilters(): array {
    return $this->filters;
  }

  /**
   * @throws InvalidInputException
   */
  public function loadFromServer(): void {
    $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->contentType = $_SERVER['CONTENT_TYPE'];
    $this->filters = is_array($_GET) ? $_GET : [];
    $this->loadPayload();
    $this->setUriSegments();
  }

  /**
   * Set URI path segments array
   */
  private function setUriSegments(): void {
    $this->uriSegments = [];
    $uri = explode('/', $this->path);
    if (is_array($uri)) {
      $this->uriSegments = $uri;
    }
  }

  /**
   * Check if route path matches request path
   * @param string $routePath
   * @return bool
   */
  public function matchPath(string $routePath): bool {
    // Perform pattern matching (e.g., "/api/users/{id}" matches "/api/users/123")
    $pattern = preg_replace('/\\\{[a-zA-Z0-9]+\\\}/', '(\\w+)', preg_quote($routePath));
    if (preg_match("#^$pattern$#", $this->path) === 1) {
      $this->extractParams($routePath);
      return true;
    }
    return false;
  }

  /**
   * Extract route parameters from path
   * @param $routePath
   * @return void
   */
  private function extractParams($routePath): void {
    $this->params = [];
    // Extract parameter values from the request path
    preg_match_all('/{([a-zA-Z0-9]+)}/', $routePath, $matches);
    $keys = $matches[1];
    $quoted = preg_quote($routePath);
    $pattern = preg_replace('/\\\{[a-zA-Z0-9]+\\\}/', '(\\w+)', $quoted);
    preg_match("#^$pattern$#", $this->path, $values);
    array_shift($values); // remove the first match (the full match)
    foreach ($keys as $index => $key) {
      $this->params[$key] = $values[$index];
    }
  }

  /**
   * @return void
   * @throws InvalidInputException
   */
  private function loadPayload(): void {
    $input = file_get_contents('php://input');
    self::parseInput($input, $_FILES, $this->payload, $this->uploads, $this->contentType);
  }

  public function hasPayload(): bool {
    return !empty($this->payload);
  }

  public function getPreparedArguments(): array {
    return $this->preparedArguments;
  }

  /**
   * @template T
   * @param class-string<T> $className
   * @return T
   */
  public function getPreparedArgument(string $className): mixed {
    foreach ($this->preparedArguments as $preparedArgument) {
      if ($preparedArgument instanceof $className) {
        return $preparedArgument;
      }
    }
    return null;
  }

  /**
   * @param class-string $className
   * @return bool
   */
  public function hasPreparedArgument(string $className): bool {
    foreach ($this->preparedArguments as $preparedArgument) {
      if ($preparedArgument instanceof $className) {
        return true;
      }
    }
    return false;
  }

  /**
   * @param callable $handler
   * @throws ReflectionException
   * @throws InvalidInputException
   * @throws ServerException
   */
  public function parseHandlerArguments(callable $handler): void {
    $requestParams = array_merge($this->getParams(), $this->getFilters());
    $requestPayload = $this->getPayload();
    $this->preparedArguments = [];

    $f = new ReflectionMethod($handler[0], $handler[1]);
    foreach ($f->getParameters() as $routeParam) {
      if (!$routeParam->hasType()) {
        throw new ServerException("Parameter '$routeParam' missing type");
      }
      $routeParamName = $routeParam->getName();
      $routeParamType = $routeParam->getType();
      if (!$routeParamType instanceof ReflectionNamedType) {
        throw new ServerException("Parameter '$routeParam' is not named type");
      }
      $routeParamTypeName = $routeParamType->getName();

      if ($routeParamTypeName === FileUpload::class && $this->hasUpload($routeParamName)) {
        $this->preparedArguments[] = $this->getUpload($routeParamName);
      } elseif (array_key_exists($routeParamName, $requestParams)) {
        // params, filters
        $this->preparedArguments[] = Reflection::castArgument($requestParams[$routeParamName], $routeParamType);
      } elseif ($this->hasPayload() && $routeParam->isVariadic()) {
        // map payload to variadic argument
        $arr = array_merge($this->preparedArguments, array_diff($requestPayload, $this->preparedArguments));
        if (!empty($this->uploads)) {
          foreach ($this->uploads as $key => $upload) {
            if (!array_key_exists($key, $arr)) {
              $arr[$key] = $upload;
            }
          }
        }
        $this->preparedArguments = $arr;
      } elseif ($this->hasPayload() && is_subclass_of($routeParamTypeName, AbstractEntity::class)) {
        // payload (map params to entity)
        $this->preparedArguments[] = $routeParamTypeName::fromPayload($requestPayload);
      } elseif ($this->hasPayload() && array_key_exists($routeParamName, $requestPayload)) {
        // payload (map param to argument)
        $this->preparedArguments[] = Reflection::castArgument($requestPayload[$routeParamName], $routeParamType);
      } elseif (!$routeParam->isOptional()) {
        throw new InvalidInputException("Missing parameter '$routeParamName' type '$routeParamTypeName'");
      }
    }
  }

  public function getUpload(string $name): ?FileUpload {
    return $this->uploads[$name] ?? null;
  }

  public function hasUpload(string $name): bool {
    return array_key_exists($name, $this->uploads);
  }

  /**
   * @throws InvalidInputException
   */
  private static function parseMultipart(array &$payload, array &$uploads, string $contentType): void {

    $input = file_get_contents('php://input');

    preg_match('/boundary=(.*)$/', $contentType, $matches);
    $boundary = $matches[1];

    $parts = explode("--$boundary", $input);

    array_shift($parts); // Remove preamble
    array_pop($parts);   // Remove epilogue

    foreach ($parts as $part) {
      $part = substr($part, 2, -2);
      [ $header, $body ] = preg_split("/\r\n\r\n/", $part, 2);

      $headers = [];
      foreach (explode("\r\n", $header) as $headerLine) {
        [ $headerName, $headerValue ] = explode(': ', $headerLine, 2);
        $headers[$headerName] = $headerValue;
      }

      // Only handle json and binary
      if (isset($headers[self::CONTENT_TYPE])) {
        $contentType = $headers[self::CONTENT_TYPE];
        if (str_starts_with($contentType, Response::TYPE_JSON)) {

          self::parseInput($body, $_FILES, $payload, $uploads, $contentType);
        } elseif (str_starts_with($headers[self::CONTENT_TYPE], 'image/')) {
          $name = 'image';
          $filename = 'upload';
          if (isset($headers[self::CONTENT_DISPOSITION])) {
            $contentDisposition = $headers[self::CONTENT_DISPOSITION];
            if (preg_match('/name=\"([^\"]*)\"/', $contentDisposition, $matches)) {
              $name = $matches[1];
            }
            if (preg_match('/filename=\"([^\"]*)\"/', $contentDisposition, $matches)) {
              $filename = $matches[1];
            }
          }

          $uploads[$name] = FileUpload::fromBuffer($body, $filename, $contentType);
        }
      }
    }

  }

  /**
   * @throws InvalidInputException
   */
  private static function parseInput(string $input, array $files, &$payload, &$uploads, string $contentType): void {
    if (!empty($input) && str_starts_with($contentType, Response::TYPE_JSON)) {
      try {
        $payload = (array) json_decode($input, true, 512, JSON_THROW_ON_ERROR);
      } catch (JsonException $e) {
        syslog(LOG_ERR, "Payload parsing failed: \"$input\" [{$e->getMessage()}]");
        throw new InvalidInputException("Payload parsing failed: \"$input\" ({$e->getMessage()})");
      }
    } elseif ($contentType === Request::TYPE_FORM_URLENCODED || str_starts_with($contentType, Request::TYPE_MULTIPART_FORM_DATA)) {
      $payload = $_POST;
      if (!empty($files)) {
        foreach ($files as $name => $file) {
          $uploads[$name] = Utils::requestFile($name);
        }
      }
    } elseif (str_starts_with($contentType, Request::TYPE_MULTIPART_RELATED)) {
      self::parseMultipart($payload, $uploads, $contentType);
    }
  }

}
