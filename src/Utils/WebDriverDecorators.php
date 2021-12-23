<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Assert;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\NavigateDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\TypeDecorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class WebDriverDecorators implements PackageInterface
{
    /**
     * @return array<string, AbstractDecorator>
     */
    public static function getDecorators(): array
    {
        $navigate = new NavigateDecorator();
        $type = new TypeDecorator();

        $click = new class extends AbstractDecorator {
            public function getClosure(array $arguments): ?Closure
            {
                $cssSelector = $arguments[0];

                return function () use ($cssSelector) {
                    $element = static::$driver->findElement(WebDriverBy::cssSelector($cssSelector));
                    $element->click();
                };
            }
        };

        $waitForAlert = new class extends AbstractDecorator {
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
        };

        $acceptAlert = new class extends AbstractDecorator {
            public function getClosure(array $arguments): ?Closure
            {
                return function () {
                    static::$driver->switchTo()->alert()->accept();
                };
            }
        };

        $clear = new class extends AbstractDecorator {
            public function getClosure(array $arguments): ?Closure
            {
                $cssSelector = $arguments[0];

                return function () use ($cssSelector) {
                    static::$driver->findElement(WebDriverBy::cssSelector($cssSelector))->clear();
                };
            }
        };

        return compact('navigate', 'type', 'click', 'waitForAlert', 'acceptAlert', 'clear');
    }
}
