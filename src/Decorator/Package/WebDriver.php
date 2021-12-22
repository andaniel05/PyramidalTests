<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Decorator\Package;

use Closure;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Assert;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\PackageInterface;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class WebDriver implements PackageInterface
{
    /**
     * @return array<string, AbstractDecorator>
     */
    public static function getDecorators(): array
    {
        $navigate = new class extends AbstractDecorator {
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
        };

        $type = new class extends AbstractDecorator {
            public function getClosure(array $arguments): ?Closure
            {
                $text = $arguments[0];
                $cssSelector = $arguments[1];

                return function () use ($text, $cssSelector) {
                    $element = static::$driver->findElement(WebDriverBy::cssSelector($cssSelector));
                    $element->sendKeys($text);
                };
            }
        };

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
