<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils\Decorator\WebDriver;

use Closure;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class NavigateDecorator extends AbstractDecorator
{
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        DSL::staticProperty('driver', null, $testCaseModel);
    }

    public function getClosure(array $arguments): ?Closure
    {
        $url = $arguments[0];

        return function () use ($url) {
            if (null === static::$driver) {
                static::$driver = RemoteWebDriver::create(
                    'http://localhost:4444/wd/hub',
                    DesiredCapabilities::chrome()
                );
            }

            static::$driver->get($url);
        };
    }
}
