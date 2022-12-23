<?php

test(function () {
    $thisClass = new ReflectionClass($this);
    $baseClass = $thisClass->getParentClass();
    $testCaseClass = $baseClass->getParentClass();

    $this->assertEquals('ThenLabs\PyramidalTests\PyramidalTestCase', $testCaseClass->getName());
});
