<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\ExpectSystemChangeTrait;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader\ReaderInterface;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SystemSnapshotInterface;

SystemSnapshot::addReader('reader1', new class implements ReaderInterface {
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
    use ExpectSystemChangeTrait;
}

setTestCaseClass(MyTestCase::class);

testCase()
    ->test(function () {
        $this->assertTrue(true);
    })

    ->testCase()
        ->test(function () {
            SystemSnapshot::addReader('reader1', new class implements ReaderInterface {
                public function getSnapshot(): array
                {
                    return [
                        'username' => 'user2',
                        'email' => 'user2@localhost.com',
                        'password' => 'user567',
                    ];
                }
            });

            $this->assertTrue(true);
        })

        ->test(function () {
            $this->expectSystemChange([
                'reader1' => [
                    'username' => 'user2',
                ],
            ]);

            $this->assertTrue(true);
        })

        ->test(function () {
            $this->expectSystemChange([
                'reader1' => [
                    'email' => 'user2@localhost.com',
                    'password' => 'user567',
                ],
            ]);

            $this->assertTrue(true);
        })
;
