<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils;

use Closure;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\Utils\Decorator\SleepDecorator;

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
        $sleep = new SleepDecorator();

        $usleep = new class extends AbstractDecorator {
            public function getClosure(array $arguments): ?Closure
            {
                $value = $arguments[0];

                return function () use ($value) {
                    sleep($value);
                };
            }
        };

        return compact('sleep', 'usleep');
    }
}
