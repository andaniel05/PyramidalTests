<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Contract\SnapshotsPerTest;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Driver\AbstractDriver;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\SystemSnapshots;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\UsefulMethodsTrait;

class MyTestCase extends TestCase implements SnapshotsPerTest
{
    use UsefulMethodsTrait;
}

setTestCaseClass(MyTestCase::class);

test(function () {
    SystemSnapshots::addDriver('myDriver', new class extends AbstractDriver {
        public function getData(): array
        {
            return [
                'key1' => 'value1',
                'key2' => 'value2',
            ];
        }

        public function reset(): void
        {
        }
    });

    static::expectCreated([
        'myDriver' => [
            'key1' => 'value1',
            'key2' => 'value2',
        ]
    ], $this);
});
