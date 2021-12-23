<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator\Sleep;

use Closure;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class USleepDecorator extends AbstractDecorator
{
    public function getClosure(array $arguments): ?Closure
    {
        $value = $arguments[0];

        return function () use ($value) {
            usleep($value);
        };
    }
}
