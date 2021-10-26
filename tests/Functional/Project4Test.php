<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional;

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
            ROOT_DIR.'/tests/Functional/projects/project4/phpunit.xml',
        );

        $outputString = exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
    }
}
