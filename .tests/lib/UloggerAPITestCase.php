<?php

use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\DbUnit\DataSet\IDataSet;

require_once("BaseDatabaseTestCase.php");

class UloggerAPITestCase extends BaseDatabaseTestCase {

  /**
   * @var null|GuzzleHttp\Client $http
   */
  protected $http;

  public function setUp(): void {
    parent::setUp();
    if (file_exists(__DIR__ . '/../.env')) {
      $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
      $dotenv->load();
      $dotenv->required(['ULOGGER_URL']);
    }

    $url = getenv('ULOGGER_URL');

    $this->http = new GuzzleHttp\Client([ 'base_uri' => $url, 'cookies' => true ]);
  }

  public function tearDown(): void {
    parent::tearDown();
    $this->http = null;
  }

  protected function getDataSet(): IDataSet {
    $this->resetAutoincrement(2);
    return $this->createFlatXMLDataSet(__DIR__ . '/../fixtures/fixture_admin.xml');
  }

  /**
   * Authenticate on server
   * @param string|null $user Login (defaults to test user)
   * @param string|null $pass Optional password (defaults to test password)
   * @return bool true on success, false otherwise
   * @throws GuzzleException
   */
  public function authenticate(?string $user = null, ?string $pass = null): bool {

    if (is_null($user)) { $user = $this->testAdminUser; }
    if (is_null($pass)) { $pass = $this->testAdminPass; }

    $options = [
      'http_errors' => false,
      'form_params' => [ 'action' => 'auth', 'user' => $user, 'pass' => $pass ],
    ];

    $response = $this->http->post('/client/index.php', $options);
    return $response->getStatusCode() === 200;
  }
}
?>
