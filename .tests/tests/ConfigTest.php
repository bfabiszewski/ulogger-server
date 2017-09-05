<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../helpers/config.php");

class ConfigTest extends TestCase {

  public function testPassRegex() {
    uConfig::$pass_lenmin = 0;
    uConfig::$pass_strength = 0;
    $password0 = "password";
    $password1 = "PASSword";
    $password2 = "PASSword1234";
    $password3 = "PASSword1234-;";

    $regex = uConfig::passRegex();
    $this->assertRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    uConfig::$pass_strength = 1;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    uConfig::$pass_strength = 2;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertNotRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    uConfig::$pass_strength = 3;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password0, "Regex: \"$regex\", password: \"$password0\"");
    $this->assertNotRegExp($regex, $password1, "Regex: \"$regex\", password: \"$password1\"");
    $this->assertNotRegExp($regex, $password2, "Regex: \"$regex\", password: \"$password2\"");
    $this->assertRegExp($regex, $password3, "Regex: \"$regex\", password: \"$password3\"");

    $password_len5 = "12345";
    $password_len10 = "1234567890";
    uConfig::$pass_lenmin = 5;
    uConfig::$pass_strength = 0;
    $regex = uConfig::passRegex();
    $this->assertRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");

    uConfig::$pass_lenmin = 7;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");

    uConfig::$pass_lenmin = 12;
    $regex = uConfig::passRegex();
    $this->assertNotRegExp($regex, $password_len5, "Regex: \"$regex\", password: \"$password_len5\"");
    $this->assertNotRegExp($regex, $password_len10, "Regex: \"$regex\", password: \"$password_len10\"");
  }
}
?>
