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

namespace Andaniel05\PyramidalTests\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Test extends Model
{
    protected $name;

    public function getName(): string
    {
        if (! $this->name) {
            $this->name = 'test' . str_replace(' ', '', ucwords($this->description));
            if (! preg_match(Model::FUNCTION_PATTERN, $this->name)) {
                $this->name = uniqid('test');
            }
        }

        return $this->name;
    }

    public function getMethodName(): string
    {
        $testName = $this->getName();
        $words = explode(' ', $testName);
        if (count($words) > 1) {
            $methodName = ucwords($testName);
            $methodName = str_replace(' ', '', $methodName);
        } else {
            $methodName = $testName;
        }

        if (strpos($methodName, 'test') !== 0) {
            $methodName = "test{$methodName}";
        }

        return $methodName;
    }
}
