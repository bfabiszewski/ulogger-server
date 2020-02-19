<?php

require_once(__DIR__ . "/../lib/UloggerAPITestCase.php");

class SetupTest extends UloggerAPITestCase {
  private $script = "/scripts/setup.php";

  public function testPrePhase() {
    $response = $this->http->get($this->script);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $body = (string) $response->getBody();
    $this->assertContains("<input type=\"hidden\" name=\"command\" value=\"setup\">", $body);
  }

  public function testSetupPhase() {
    $options = [
      "http_errors" => false,
      "form_params" => [ "command" => "setup" ]
    ];
    $response = $this->http->post($this->script, $options);
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $body = (string) $response->getBody();
    $this->assertContains("<input type=\"hidden\" name=\"command\" value=\"adduser\">", $body);
  }

  public function testAdduserPhase() {
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
    $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code");
    $body = (string) $response->getBody();
    $this->assertContains("<span class=\"ok\">", $body);
    $this->assertEquals(2, $this->getConnection()->getRowCount("users"), "Wrong row count");
    $expected = [ "id" => 2, "login" => $this->testUser, "admin" => 1 ];
    $actual = $this->getConnection()->createQueryTable("users", "SELECT id, login, admin FROM users WHERE id = 2");
    $this->assertTableContains($expected, $actual, "Wrong actual table data");
    $this->assertTrue(password_verify($this->testPass, $this->pdoGetColumn("SELECT password FROM users WHERE id = 2")), "Wrong actual password hash");
  }

}

?>