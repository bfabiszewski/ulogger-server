<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2025 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Tests\Script;

use PHPUnit\Framework\TestCase;

final class ImportCliTest extends TestCase {
  /** @var string */
  private string $scriptPath;

  protected function setUp(): void {
    $this->scriptPath = realpath(__DIR__ . '/../../scripts/import_cli.php');
    if ($this->scriptPath === false) {
      $this->markTestSkipped('CLI script not found.');
    }
  }

  public function testHelpOptionDisplaysUsage(): void {
    // Run the script with the --help option.
    $command = escapeshellcmd("php $this->scriptPath --help");
    exec($command, $output, $exitCode);

    $outputStr = implode("\n", $output);
    // We expect a help/usage text (e.g., containing "Usage" or "help")
    $this->assertStringContainsStringIgnoringCase('usage', $outputStr);
    // Exit code should be 0.
    $this->assertSame(0, $exitCode);
  }

  public function testNonExistingGpxFileCausesError(): void {
    // Provide a filename that does not exist.
    $nonExistingFile = 'nonexistent_file.gpx';
    $command = escapeshellcmd("php $this->scriptPath $nonExistingFile");
    exec($command, $output, $exitCode);

    $outputStr = implode("\n", $output);
    // Expect an error message about the file not being readable.
    $this->assertStringContainsStringIgnoringCase('is not readable', $outputStr);
    // Exit code should be non‑zero.
    $this->assertNotSame(0, $exitCode);
  }

  public function testInvalidArgumentsCauseError(): void {
    // Run the script without the required operand(s)
    $command = escapeshellcmd("php $this->scriptPath");
    exec($command, $output, $exitCode);

    $outputStr = implode("\n", $output);
    $this->assertStringContainsStringIgnoringCase('error', $outputStr);
    $this->assertNotSame(0, $exitCode);
  }

}
