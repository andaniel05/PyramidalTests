<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator;

use Closure;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SleepDecorator extends AbstractDecorator
{
    public function getClosure(array $arguments): ?Closure
    {
        $seconds = $arguments[0];

        return function () use ($seconds) {
            sleep($seconds);
        };
    }
}
