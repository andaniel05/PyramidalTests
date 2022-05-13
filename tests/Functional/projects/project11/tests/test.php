<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Contract\SnapshotsPerTestCase;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Driver\AbstractDriver;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\SystemSnapshots;

class MyTestCase extends TestCase implements SnapshotsPerTestCase
{
}

setTestCaseClass(MyTestCase::class);

setUpBeforeClass(function () {
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
});

test(function () {
    $expectations = [
        'CREATED' => [
            'myDriver' => [
                'key1' => 'value1',
            ],
        ],
    ];

    $context = static::class;

    SystemSnapshots::expect($expectations, $context);
});
