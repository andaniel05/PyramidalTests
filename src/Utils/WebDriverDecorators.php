<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\AcceptAlertDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\ClickDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\NavigateDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\TypeDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\WaitForAlertDecorator;

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
        $click = new ClickDecorator();
        $waitForAlert = new WaitForAlertDecorator();
        $acceptAlert = new AcceptAlertDecorator();

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
