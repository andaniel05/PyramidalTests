<?php

test(function () {
    $reflectionClass = new ReflectionClass($this);
    $parentClass = $reflectionClass->getParentClass();

    $this->assertEquals('PHPUnit\Framework\TestCase', $parentClass->getName());
});
