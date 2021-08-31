<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MacroNotFoundException extends PyramidalTestsException
{
    public function __construct(string $title)
    {
        parent::__construct("The macro with name '{$title}' is not found.");
    }
}
