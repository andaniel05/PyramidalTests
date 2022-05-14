<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Contract\SnapshotsPerTestCase;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Driver\AbstractDriver;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\SystemSnapshots;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\UsefulMethodsTrait;

class MyTestCase extends TestCase implements SnapshotsPerTestCase
{
    use UsefulMethodsTrait;
}

setTestCaseClass(MyTestCase::class);

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

setUpBeforeClass(function () {
    SystemSnapshots::addDriver('myDriver', new class extends AbstractDriver {
        public function getData(): array
        {
            return [
                'key2' => 'value22',
                'key3' => 'value3',
            ];
        }

        public function reset(): void
        {
        }
    });

    static::expectCreated(['myDriver' => ['key3' => 'value3']]);
    static::expectUpdated(['myDriver' => ['key2' => 'value22']]);
    static::expectDeleted(['myDriver' => ['key1' => 'value1']]);
});

test(function () {
    $this->assertTrue(true);
});
