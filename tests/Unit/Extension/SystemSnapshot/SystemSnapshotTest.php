<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit\Extension\SystemSnapshot;

use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader\DatabaseReaderInterface;
use ThenLabs\PyramidalTests\Tests\Unit\UnitTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SystemSnapshotTest extends UnitTestCase
{
    public function testTruncateTables()
    {
        $dbReader1 = $this->createMock(DatabaseReaderInterface::class);
        $dbReader1->expects($this->once())->method('truncateTables');

        $dbReader2 = $this->createMock(DatabaseReaderInterface::class);
        $dbReader2->expects($this->once())->method('truncateTables');

        SystemSnapshot::addReader('db1', $dbReader1);
        SystemSnapshot::addReader('db2', $dbReader2);

        SystemSnapshot::truncateTables();
    }
}
