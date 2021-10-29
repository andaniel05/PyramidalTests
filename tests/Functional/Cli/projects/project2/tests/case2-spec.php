<?php

test(function () {
    $thisClass = new ReflectionClass($this);
    $baseClass = $thisClass->getParentClass();
    $testCaseClass = $baseClass->getParentClass();

    $this->assertEquals('PHPUnit\Framework\TestCase', $testCaseClass->getName());
});
