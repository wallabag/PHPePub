<?php

namespace PHPePub\Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ExampleUsageTest extends TestCase {

    /**
     * @return void
     * @dataProvider exampleDataProvider
     *
     */
    public function testExampleUsage($exampleScript) {
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        $phpBinary = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php' . $phpVersion;
        $phpCommand = file_exists($phpBinary) ? $phpBinary : 'php';

        $process = proc_open($phpCommand . ' ' . escapeshellarg($exampleScript),
            $descriptorspec,
            $pipes);

        $this->assertNotFalse($process, "Failed to execute example script");

        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $exitCode = proc_close($process);

        $this->assertEquals(0,
            $exitCode,
            "Example script failed with errors: " . $errors);
        $this->assertNotEmpty($output, "Example script produced no output");
    }

    public function exampleDataProvider() {
        $examplesPath = __DIR__ . '/../../fixtures/examples';
        $files = [];

        if (is_dir($examplesPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($examplesPath)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = [$file->getPathname()];
                }
            }
        }

        return $files;
    }
}
