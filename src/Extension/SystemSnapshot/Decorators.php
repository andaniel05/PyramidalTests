<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot;

use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Decorator\ExpectSystemChangeDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Decorators implements PackageInterface
{
    public static function getDecorators(): array
    {
        return [
            'expectSystemChange' => new ExpectSystemChangeDecorator(),
        ];
    }
}
