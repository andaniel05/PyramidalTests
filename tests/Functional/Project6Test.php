<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project6Test extends TestCase
{
    public function test1()
    {
        $command = sprintf(
            "php %s -c %s --testsuite=Suite2",
            ROOT_DIR.'/bin/pyramidal',
            __DIR__.'/projects/project6/phpunit.xml',
        );

        $outputString = exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (1 test', $outputString);
    }
}
