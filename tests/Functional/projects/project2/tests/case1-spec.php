<?php

class TestCase1 extends \PHPUnit\Framework\TestCase
{
}

setTestCaseClass('TestCase1');

testCase(function () {
    foreach (range(1, 2) as $i) {
        test(function () {
            $reflectionClass = new ReflectionClass($this);
            $parentClass = $reflectionClass->getParentClass();

            $this->assertEquals('TestCase1', $parentClass->getName());
        });
    }
});
