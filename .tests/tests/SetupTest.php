<?php

use GuzzleHttp\Exception\GuzzleException;

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");

class SetupTest extends UloggerAPITestCase {
  private $script = "/scripts/setup.php";

  /**
   * @throws GuzzleException
   */
  public function testPrePhase(): void {
    $response = $this->http->get($this->script);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $body = (string) $response->getBody();
    self::assertStringContainsString("<input type=\"hidden\" name=\"command\" value=\"setup\">", $body);
  }

  /**
   * @throws GuzzleException
   */
  public function testSetupPhase(): void {
    $options = [
      "http_errors" => false,
      "form_params" => [ "command" => "setup" ]
    ];
    $response = $this->http->post($this->script, $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $body = (string) $response->getBody();
    self::assertStringContainsString("<input type=\"hidden\" name=\"command\" value=\"adduser\">", $body);
  }

  /**
   * @throws GuzzleException
   */
  public function testAdduserPhase(): void {
    $options = [
      "http_errors" => false,
      "form_params" => [
        "command" => "adduser",
        "login" => $this->testUser,
        "pass" => $this->testPass,
        "pass2" => $this->testPass
      ]
    ];
    $response = $this->http->post($this->script, $options);
    self::assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $body = (string) $response->getBody();
    self::assertStringContainsString("<span class=\"ok\">", $body);
    self::assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $expected = [ "id" => 2, "login" => $this->testUser, "admin" => 1 ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, login, admin FROM users WHERE id = 2");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    self::assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = 2")), "Wrong actual password hash");
  }

}

?>