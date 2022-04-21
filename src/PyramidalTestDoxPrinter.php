<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests;

use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use ThenLabs\PyramidalTests\Model\Record;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class PyramidalTestDoxPrinter extends CliTestDoxPrinter
{
    protected function writeTestResult(array $prevResult, array $result): void
    {
        $testNameParts = explode('::', $result['testName']);
        $fcqn = $testNameParts[0];
        $testCaseModel = $this->getTestCaseModel($fcqn);
        $level = count($testCaseModel->getParents());

        if ($level > 0) {
            $margin = $this->getMargin($level);

            ob_start();
            parent::writeTestResult($prevResult, $result);
            $originalString = ob_get_clean();

            $lines = explode(PHP_EOL, $originalString);
            $lines = array_map(function ($line) use ($margin) {
                return empty($line) ? $line : $margin.$line;
            }, $lines);

            echo implode(PHP_EOL, $lines);
        } else {
            parent::writeTestResult($prevResult, $result);
        }
    }

    protected function getMargin(int $level): string
    {
        $result = '';

        for ($i = 0; $i < $level; $i++) {
            $result .= '    ';
        }

        return $result;
    }

    protected function getTestCaseModel(string $fcqn): ?TestCaseModel
    {
        $result = null;

        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            if ($fcqn == $testCaseModel->getClassBuilder()->getFCQN()) {
                return $testCaseModel;
            } else {
                foreach ($testCaseModel->children() as $childTestCaseModel) {
                    if ($childTestCaseModel instanceof TestCaseModel &&
                        $fcqn == $childTestCaseModel->getClassBuilder()->getFCQN()
                    ) {
                        return $childTestCaseModel;
                    }
                }
            }
        }

        return $result;
    }
}
