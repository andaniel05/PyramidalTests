<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils;

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\Utils\Decorator\Sleep\SleepDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\Sleep\USleepDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SleepDecorators implements PackageInterface
{
    /**
     * @return array<string, AbstractDecorator>
     */
    public static function getDecorators(): array
    {
        return [
            'sleep' => new SleepDecorator(),
            'usleep' => new USleepDecorator(),
        ];
    }
}
