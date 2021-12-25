<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project1Test extends TestCase
{
    public function test1()
    {
        copy(
            __DIR__.'/app/template.sqlite',
            __DIR__.'/app/db.sqlite'
        );

        $command = sprintf(
            "php %s -c %s",
            ROOT_DIR.'/bin/pyramidal',
            ROOT_DIR.'/tests/Integration/projects/project1/phpunit.xml',
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $resultString = implode("\n", $outputArray);

        $expected = <<<TXT
        Unexpected Array Diff:
        [
            'db' => [
                'sqlite_sequence' => [
                    [
                        'seq' => '3'
                    ]
                ],
                'persons' => [
                    2 => [
                        'id' => '3',
                        'name' => 'Antonio',
                        'age' => '60'
                    ]
                ]
            ]
        ]
        TXT;

        $this->assertSame(1, $returnValue);
        $this->assertStringContainsString($expected, $resultString);
    }
}
