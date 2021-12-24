<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface ReaderInterface
{
    public function getSnapshot(): array;
}
