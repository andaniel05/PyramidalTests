<?php

use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader\ReaderInterface;

$snapshotReader1 = new class implements ReaderInterface {
    public function getSnapshot(): array
    {
        return range(1, 10);
    }
};

$snapshotReader2 = new class implements ReaderInterface {
    public function getSnapshot(): array
    {
        return range(1, 50);
    }
};

SystemSnapshot::addReader('reader1', $snapshotReader1);
SystemSnapshot::addReader('reader2', $snapshotReader2);

testCase()
    ->test(function () {
        $expectedSystemSnapshot = [
            'reader1' => range(1, 10),
            'reader2' => range(1, 50),
        ];

        $this->assertEquals($expectedSystemSnapshot, SystemSnapshot::getSnapshot());
    });
;
