<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator\WebDriver;

use Closure;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AcceptAlertDecorator extends AbstractDecorator
{
    public function getClosure(array $arguments): ?Closure
    {
        return function () {
            static::$driver->switchTo()->alert()->accept();
        };
    }
}
