<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project10Test extends TestCase
{
    public function test()
    {
        $command = sprintf(
            "php %s -c %s --testdox",
            ROOT_DIR.'/bin/pyramidal',
            __DIR__.'/projects/project10/phpunit.xml',
        );

        exec($command, $outputArray, $returnValue);

        $output = implode(PHP_EOL, $outputArray);

        $expectedOutput = <<<'TXT'
           │ Unexpectations in snapshots:
           │ [
           │     'CREATED' => [
           │         'myDriver' => [
           │             'key2' => 'value2'
           │         ]
           │     ]
           │ ]
        TXT;

        $this->assertGreaterThan(0, $returnValue);
        $this->assertStringContainsString($expectedOutput, $output);
    }
}
