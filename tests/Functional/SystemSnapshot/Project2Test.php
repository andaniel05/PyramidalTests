<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional\SystemSnapshot;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project2Test extends TestCase
{
    public function test1()
    {
        $command = sprintf(
            "php %s -c %s",
            ROOT_DIR.'/bin/pyramidal',
            ROOT_DIR.'/tests/Functional/SystemSnapshot/projects/project2/phpunit.xml',
            __DIR__.'/projects/project2/phpunit.xml',
        );

        exec($command, $outputArray, $returnValue);

        $outputString = implode("\n", $outputArray);

        $expectedErrorMessage = <<<'TXT'
        Unexpected Array Diff:
        [
            'reader1' => [
                'password' => 'user567'
            ]
        ]
        TXT;

        $this->assertSame(2, $returnValue);
        $this->assertStringContainsString($expectedErrorMessage, $outputString);
    }
}
