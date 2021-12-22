<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Decorator\Package;

use Closure;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Sleep implements PackageInterface
{
    /**
     * @return array<string, AbstractDecorator>
     */
    public static function getDecorators(): array
    {
        $sleep = new class extends AbstractDecorator {
            public function getClosure(array $arguments): ?Closure
            {
                $seconds = $arguments[0];

                return function () use ($seconds) {
                    sleep($seconds);
                };
            }
        };

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
