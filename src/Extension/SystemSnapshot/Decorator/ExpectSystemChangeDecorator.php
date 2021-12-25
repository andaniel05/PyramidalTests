<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot\Decorator;

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ExpectSystemChangeDecorator extends AbstractDecorator
{
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        $firstArgument = $arguments[0];
        $secondArgument = $arguments[1] ?? null;

        $title = '';
        $expectations = [];

        if (is_string($firstArgument) && is_array($secondArgument)) {
            $title = $firstArgument;
            $expectations = $secondArgument;
        } elseif (is_array($firstArgument)) {
            $expectations = $firstArgument;
        }

        $testCaseModel->addDiffExpectationsForSystemSnapshot($expectations);

        $closure = function () use ($expectations) {
            $this->expectSystemChange($expectations);

            $this->assertTrue(true);
        };

        DSL::test($title, $closure, $testCaseModel);
    }
}
