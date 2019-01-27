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

namespace Andaniel05\PyramidalTests\Tests\Utils;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Andaniel05\PyramidalTests\Utils\StaticVarsInjectionTrait;

class StaticVarsInjectionTraitTest extends PHPUnitTestCase
{
    public function setUp()
    {
        $this->entity = new class { use StaticVarsInjectionTrait; };
        $this->entityClass = get_class($this->entity);
        $this->entityClass::resetVars();
    }

    public function testSetAndGetVar()
    {
        $name = uniqid('var');
        $value = uniqid();

        $this->entityClass::setVar($name, $value);

        $this->assertEquals($value, $this->entityClass::getVar($name));
    }

    public function testInjectVars()
    {
        $name = uniqid('var');
        $value = uniqid();

        $this->entityClass::setVar($name, $value);
        $this->entity->injectVars();

        $this->assertEquals($value, $this->entity->{$name});
    }

    public function testAddVars()
    {
        $name1 = uniqid('var');
        $value1 = uniqid();
        $name2 = uniqid('var');
        $value2 = uniqid();
        $value3 = uniqid();

        $this->entityClass::setVar($name1, $value1);
        $this->entityClass::addVars([
            $name1 => $value2,
            $name2 => $value3,
        ]);

        $this->assertEquals([$name1 => $value2, $name2 => $value3], $this->entityClass::getAllVars());
    }

    public function testGetAllVars()
    {
        $name1 = uniqid('var');
        $value1 = uniqid();
        $name2 = uniqid('var');
        $value2 = uniqid();

        $this->entityClass::setVar($name1, $value1);
        $this->entityClass::setVar($name2, $value2);

        $this->assertEquals([$name1 => $value1, $name2 => $value2], $this->entityClass::getAllVars());
    }
}
