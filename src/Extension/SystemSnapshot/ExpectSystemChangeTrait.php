<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot;

use ThenLabs\PyramidalTests\Extension\SystemSnapshot;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait ExpectSystemChangeTrait
{
    public function expectSystemChange(array $expectations): void
    {
        $className = get_class($this);

        SystemSnapshot::addDiffExpectation($className, $expectations);
    }
}
