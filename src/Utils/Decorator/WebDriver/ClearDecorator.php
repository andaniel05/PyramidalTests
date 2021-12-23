<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator\WebDriver;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ClearDecorator extends AbstractDecorator
{
    public function getClosure(array $arguments): ?Closure
    {
        $cssSelector = $arguments[0];

        return function () use ($cssSelector) {
            static::$driver->findElement(WebDriverBy::cssSelector($cssSelector))->clear();
        };
    }
}
