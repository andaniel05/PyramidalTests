<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project3Test extends TestCase
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
            ROOT_DIR.'/tests/Integration/projects/project3/phpunit.xml',
        );

        exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
    }
}
