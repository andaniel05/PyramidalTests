<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils;

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\AcceptAlertDecorator;
use ThenLabs\PyramidalTests\Utils\Decorator\WebDriver\ClearDecorator;
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
        return [
            'navigate'     => new NavigateDecorator(),
            'type'         => new TypeDecorator(),
            'click'        => new ClickDecorator(),
            'waitForAlert' => new WaitForAlertDecorator(),
            'acceptAlert'  => new AcceptAlertDecorator(),
            'clear'        => new ClearDecorator(),
        ];
    }
}
