<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Project1Test extends TestCase
{
    public function test1()
    {
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
        );

        $process = proc_open(
            ROOT_DIR.'/pyramidal --testdox',
            $descriptorspec,
            $pipes,
            __DIR__.'/projects/project1'
        );

        $this->assertIsResource($process);

        $output = stream_get_contents($pipes[1]);

        fclose($pipes[0]);
        fclose($pipes[1]);

        $returnValue = proc_close($process);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (6 tests', $output);
        $this->assertStringContainsString('my parent test case', $output);
        $this->assertStringContainsString('my test 2', $output);
        $this->assertStringContainsString('my first test', $output);
    }

    public function test2()
    {
        $command = sprintf(
            "php %s -c %s",
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
        );

        $outputString = exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (6 tests', $outputString);
    }

    public function provider1(): array
    {
        return [
            ["'my first test'"],
            ['"my first test"'],
        ];
    }

    /**
     * @dataProvider provider1
     */
    public function test3(string $arg)
    {
        $command = sprintf(
            "php %s -c %s --filter %s",
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
            $arg
        );

        $outputString = exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (1 test', $outputString);
    }

    /**
     * @dataProvider provider1
     */
    public function test4(string $arg)
    {
        $command = sprintf(
            "php %s -c %s --filter=%s",
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
            $arg
        );

        $outputString = exec($command, $outputArray, $returnValue);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (1 test', $outputString);
    }

    public function test5()
    {
        $command = sprintf(
            "php %s -c %s --filter 'my parent test case' --testdox",
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml'
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $outputString = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (3 tests', $outputString);
        $this->assertStringContainsString('my first test', $outputString);
        $this->assertStringContainsString('my test 2', $outputString);
        $this->assertStringContainsString('my test 3', $outputString);
    }

    public function test6()
    {
        $command = sprintf(
            'php %s -c %s --filter="my child test case"',
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml'
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $outputString = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (2 tests', $outputString);
    }

    public function test7()
    {
        $command = sprintf(
            'php %s -c %s --testdox --filter="%s"',
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
            ROOT_DIR.'/tests/Functional/projects/project1/tests/test-1.php:3',
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $outputString = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (3 tests', $outputString);
        $this->assertStringContainsString('my first test', $outputString);
        $this->assertStringContainsString('my test 2', $outputString);
        $this->assertStringContainsString('my test 3', $outputString);
    }

    public function test8()
    {
        $command = sprintf(
            'php %s -c %s --testdox --filter="%s"',
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
            ROOT_DIR.'/tests/Functional/projects/project1/tests/test-1.php:25',
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $outputString = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (1 test', $outputString);
        $this->assertStringContainsString('my end test', $outputString);
    }

    public function test9()
    {
        $command = sprintf(
            'php %s -c %s --testdox --filter="%s"',
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
            ROOT_DIR.'/tests/Functional/projects/project1/tests/test-1.php:26',
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $outputString = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (1 test', $outputString);
        $this->assertStringContainsString('my end test', $outputString);
    }

    public function test10()
    {
        $command = sprintf(
            'php %s -c %s --testdox --filter="%s"',
            ROOT_DIR.'/pyramidal',
            ROOT_DIR.'/tests/Functional/projects/project1/phpunit.xml',
            ROOT_DIR.'/tests/Functional/projects/project1/tests/test-1.php:12',
        );

        $outputString = exec($command, $outputArray, $returnValue);
        $outputString = implode(PHP_EOL, $outputArray);

        $this->assertSame(0, $returnValue);
        $this->assertStringContainsString('OK (1 test', $outputString);
        $this->assertStringContainsString('my parent test case', $outputString);
        $this->assertStringContainsString('my test 3', $outputString);
    }
}
