<?php

class TestCase1 extends \PHPUnit\Framework\TestCase
{
}

setTestCaseClass('TestCase1');

testCase(function () {
    foreach (range(1, 2) as $i) {
        test(function () {
            $this->assertInstanceOf('TestCase1', $this);
        });
    }
});
