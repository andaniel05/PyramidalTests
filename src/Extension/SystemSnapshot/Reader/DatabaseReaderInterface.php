<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface DatabaseReaderInterface extends ReaderInterface
{
    public function getTables(): array;

    public function truncateTables(): void;

    public function getSchema(): array;
}
