<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional\SystemSnapshot;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project4Test extends TestCase
{
    public function test1()
    {
        $command = sprintf(
            "php %s -c %s",
            ROOT_DIR.'/bin/pyramidal',
            ROOT_DIR.'/tests/Functional/SystemSnapshot/projects/project4/phpunit.xml',
            __DIR__.'/projects/project4/phpunit.xml',
        );

        exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
    }
}
