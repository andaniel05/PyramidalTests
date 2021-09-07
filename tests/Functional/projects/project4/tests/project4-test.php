<?php

use PHPUnit\Framework\Assert;

testCase(function () {
    staticProperty('myStaticProperty', 'myStaticPropertyValue');
    property('myProperty', 'myPropertyValue');

    staticMethod('getMyStaticProperty', function () {
        return static::$myStaticProperty;
    });

    method('getMyProperty', function () {
        return $this->myProperty;
    });

    setUpBeforeClassOnce(function () {
        Assert::assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
    });

    setUp(function () {
        $this->assertEquals('myPropertyValue', $this->getMyProperty());
    });

    test(function () {
        $this->assertEquals('myPropertyValue', $this->getMyProperty());
    });

    tearDown(function () {
        $this->assertEquals('myPropertyValue', $this->getMyProperty());
    });

    tearDownAfterClassOnce(function () {
        Assert::assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            Assert::assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
        });

        setUp(function () {
            $this->assertEquals('myPropertyValue', $this->getMyProperty());
        });

        test(function () {
            $this->assertEquals('myPropertyValue', $this->getMyProperty());
        });

        tearDown(function () {
            $this->assertEquals('myPropertyValue', $this->getMyProperty());
        });

        tearDownAfterClassOnce(function () {
            Assert::assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
        });
    });
});
