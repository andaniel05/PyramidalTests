<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model\Decorator;

use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractDecorator
{
    abstract public function decorate(TestCaseModel $testCaseModel, array $arguments);
}
