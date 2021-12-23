<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator\WebDriver;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TypeDecorator extends AbstractDecorator
{
    public function getClosure(array $arguments): ?Closure
    {
        $text = $arguments[0];
        $cssSelector = $arguments[1];

        return function () use ($text, $cssSelector) {
            $element = static::$driver->findElement(WebDriverBy::cssSelector($cssSelector));
            $element->sendKeys($text);
        };
    }
}
