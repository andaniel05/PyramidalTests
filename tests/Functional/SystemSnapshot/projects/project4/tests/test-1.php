<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\ExpectSystemChangeDecorator;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\ExpectSystemChangeTrait;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\ReaderInterface;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SystemSnapshotInterface;

SystemSnapshot::addReader('reader1', new class implements ReaderInterface {
    public function getSnapshot(): array
    {
        return [
            'username' => uniqid(),
        ];
    }
});

class MyTestCase extends TestCase implements SystemSnapshotInterface
{
    use ExpectSystemChangeTrait;
}

setTestCaseClass(MyTestCase::class);

testCase()
    ->importDecorators(ExpectSystemChangeDecorator::class)
    ->expectSystemChange('the system has the expected changes', [
        'reader1' => [
            'username' => fn () => true,
        ],
    ])
;

testCase()
    ->importDecorators(ExpectSystemChangeDecorator::class)
    ->expectSystemChange([
        'reader1' => [
            'username' => fn () => true,
        ],
    ])
;
