<?php
declare(strict_types=1);

/*
    Copyright (C) <2018>  <Andy Daniel Navarro Taño>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Andaniel05\PyramidalTests\Utils;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait StaticVarsInjectionTrait
{
    protected static $vars = [];

    public function injectVars(): void
    {
        foreach (static::$vars as $name => $value) {
            $this->{$name} = $value;
        }
    }

    public static function setVar(string $name, $value): void
    {
        static::$vars[$name] = $value;
    }

    public static function getVar(string $name)
    {
        return static::$vars[$name] ?? null;
    }

    public static function getAllVars(): array
    {
        return static::$vars;
    }

    public static function addVars(array $vars): void
    {
        foreach ($vars as $name => $value) {
            static::setVar($name, $value);
        }
    }

    public static function resetVars(): void
    {
        static::$vars = [];
    }
}
