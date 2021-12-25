<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot;

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Model\Record;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait ExpectSystemChangeTrait
{
    public function expectSystemChange(array $expectations): void
    {
        if (! $this instanceof TestCase) {
            return;
        }

        $thisClass = get_class($this);

        if ($this instanceof SnapshotsPerTestsInterface) {
            SystemSnapshot::addDiffExpectation($thisClass, $expectations);
        } /* elseif ($this instanceof SnapshotsInDecoratorsInterface) {
            $aux = function ($testCaseModel) use ($thisClass, $expectations, &$aux) {
                $testCaseClass = $testCaseModel->getClassBuilder()->getFCQN();

                if ($thisClass === $testCaseClass) {
                    $testCaseModel->addDiffExpectationsForSystemSnapshot($expectations);
                    return true;
                } else {
                    foreach ($testCaseModel->children() as $child) {
                        $aux($child);
                    }
                }
            };

            foreach (Record::getAllTestCaseModels() as $rootTestCaseModel) {
                if (true === $aux($rootTestCaseModel)) {
                    return;
                }
            }
        } */
    }
}
