<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project8Test extends TestCase
{
    public function test()
    {
        $command = sprintf(
            "php %s -c %s --testdox",
            ROOT_DIR.'/bin/pyramidal',
            __DIR__.'/projects/project8/phpunit.xml',
        );

        exec($command, $outputArray, $returnValue);

        $output = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
    }
}
