<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Decorator\Package;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface PackageInterface
{
    /**
     * @return array<string, AbstractDecorator>
     */
    public static function getDecorators(): array;
}
