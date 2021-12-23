<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator\WebDriver;

use Closure;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Assert;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class WaitForAlertDecorator extends AbstractDecorator
{
    public function getClosure(array $arguments): ?Closure
    {
        $text = $arguments[0] ?? null;

        return function () use ($text) {
            static::$driver->wait()->until(WebDriverExpectedCondition::alertIsPresent());

            $alert = static::$driver->switchTo()->alert();

            if ($text) {
                Assert::assertEquals($text, $alert->getText());
            }
        };
    }
}
