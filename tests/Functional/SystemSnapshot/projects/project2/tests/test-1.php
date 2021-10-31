<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SnapshotReaderInterface;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SystemSnapshotInterface;

SystemSnapshot::registerSnapshotReader('reader1', new class implements SnapshotReaderInterface {
    public function getSnapshot(): array
    {
        return [
            'username' => 'user1',
            'email' => 'user1@localhost.com',
            'password' => 'user123',
        ];
    }
});

class MyTestCase extends TestCase implements SystemSnapshotInterface
{
}

setTestCaseClass(MyTestCase::class);

testCase()
    ->test(function () {
        $this->assertTrue(true);
    })

    ->test(function () {
        $this->assertTrue(true);
    })

    ->test(function () {
        SystemSnapshot::registerSnapshotReader('reader1', new class implements SnapshotReaderInterface {
            public function getSnapshot(): array
            {
                return [
                    'username' => 'user1',
                    'email' => 'user1@localhost.com',
                    'password' => 'user567',
                ];
            }
        });

        $this->assertTrue(true);
    })

    ->testCase()
        ->test(function () {
            $this->assertTrue(true);
        })
;
