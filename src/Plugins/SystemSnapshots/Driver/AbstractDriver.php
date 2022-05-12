<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Driver;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractDriver
{
    abstract public function getData(): array;

    abstract public function reset(): void;
}
