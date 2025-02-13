<?php
declare(strict_types = 1);

namespace uLogger\Tests\API;

use Dotenv;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use uLogger\Tests\Mapper\AbstractMapperTestCase;

abstract class AbstractAPITestCase extends AbstractMapperTestCase {

  /**
   * @var null|GuzzleHttp\Client $http
   */
  protected ?GuzzleHttp\Client $http;

  public function setUp(): void {
    parent::setUp();
    if (file_exists(__DIR__ . '/../.env')) {
      $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
      $dotenv->load();
      $dotenv->required([ 'ULOGGER_URL' ]);
    }

    $url = getenv('ULOGGER_URL');

    $this->http = new GuzzleHttp\Client([ 'base_uri' => $url, 'cookies' => true ]);
  }

  public function tearDown(): void {
    parent::tearDown();
    $this->http = null;
  }

  /**
   * Authenticate on server
   * @param string|null $login Login (defaults to admin user)
   * @param string|null $password Optional password (defaults to admin password)
   * @return bool true on success, false otherwise
   * @throws GuzzleException
   */
  protected function authenticate(?string $login = null, ?string $password = null): bool {
    $response = $this->httpPost('/api/session', [
      'json' => [
        'login' => $login ?? self::TEST_ADMIN_USER, 'password' => $password ?? self::TEST_ADMIN_PASS
      ]
    ]);
    return $response->getStatusCode() === 201;
  }

  /**
   * Authenticate on server using legacy authentication
   * @param string|null $login Login (defaults to admin user)
   * @param string|null $password Optional password (defaults to admin password)
   * @return bool true on success, false otherwise
   * @throws GuzzleException
   */
  protected function authenticateLegacy(?string $login = null, ?string $password = null): bool {
    $response = $this->httpPost('/client/index.php', [
      'form_params' => [
        'action' => 'auth', 'user' => $login ?? self::TEST_ADMIN_USER, 'pass' => $password ?? self::TEST_ADMIN_PASS
      ]
    ]);
    return $response->getStatusCode() === 200;
  }

  /**
   * @param string $url
   * @param array $options
   * @return ResponseInterface
   * @throws GuzzleException
   */
  private function httpPost(string $url, array $options = []): ResponseInterface {
    $options['http_errors'] = false;
    return $this->http->post($url, $options);
  }

  // helper methods

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @param int $expectedStatusCode
   * @param bool $isExpectedResponseNull
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  private function performRequestAndValidate(string $url, string $method, ?array $payload, int $expectedStatusCode, bool $isExpectedResponseNull = true): stdClass|array|null {
    $options = ['http_errors' => false];
    if (!empty($payload)) {
      $options['json'] = $payload;
    }
    $response = $this->http->{$method}($url, $options);
    $this->assertEquals($expectedStatusCode, $response->getStatusCode(), 'Unexpected status code');
    $json = json_decode((string) $response->getBody());
    if ($isExpectedResponseNull) {
      $this->assertNull($json, 'Response is not null');
    } else {
      $this->assertNotNull($json, 'Response is null');
    }
    return $json;
  }

  /**
   * @param string $url
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  protected function performGetRequestSuccess(string $url): stdClass|array|null {
    return $this->performRequestAndValidate($url, 'get', null, 200, false);
  }

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @throws GuzzleException
   */
  protected function performRequestUnauthorized(string $url, string $method, ?array $payload = []): void {
    $this->performRequestAndValidate($url, $method, $payload, 401);
  }

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @throws GuzzleException
   */
  protected function performRequestNoContent(string $url, string $method, ?array $payload = []): void {
    $this->performRequestAndValidate($url, $method, $payload, 204);
  }

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @throws GuzzleException
   */
  protected function performRequestNotFound(string $url, string $method, ?array $payload = []): void {
    $this->performRequestAndValidate($url, $method, $payload, 404);
  }

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  protected function performRequestUnprocessable(string $url, string $method, ?array $payload = []): stdClass|array|null {
    return $this->performRequestAndValidate($url, $method, $payload, 422, false);
  }

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  protected function performRequestCreated(string $url, string $method, ?array $payload = []): stdClass|array|null {
    return $this->performRequestAndValidate($url, $method, $payload, 201, false);
  }

  /**
   * @param string $url
   * @param string $method
   * @param array|null $payload
   * @return stdClass|array|null
   * @throws GuzzleException
   */
  protected function performRequestConflict(string $url, string $method, ?array $payload = []): stdClass|array|null {
    return $this->performRequestAndValidate($url, $method, $payload, 409, false);
  }
}

?>
