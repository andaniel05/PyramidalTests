<?php

use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SnapshotReaderInterface;

$snapshotReader1 = new class implements SnapshotReaderInterface {
    public function getSnapshot(): array
    {
        return range(1, 10);
    }
};

$snapshotReader2 = new class implements SnapshotReaderInterface {
    public function getSnapshot(): array
    {
        return range(1, 50);
    }
};

SystemSnapshot::registerSnapshotReader('reader1', $snapshotReader1);
SystemSnapshot::registerSnapshotReader('reader2', $snapshotReader2);

testCase()
    ->test(function () {
        $expectedSystemSnapshot = [
            'reader1' => range(1, 10),
            'reader2' => range(1, 50),
        ];

        $this->assertEquals($expectedSystemSnapshot, SystemSnapshot::getSystemSnapshot());
    });
;
