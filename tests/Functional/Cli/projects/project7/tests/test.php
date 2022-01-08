<?php

describe('my test case', function () {
    staticProperty('myStaticProperty');

    beforeAll(function () {
        static::$myStaticProperty = 'myStaticPropertyValue';
    });

    beforeEach(function () {
        $this->myProperty = 'myPropertyValue';
    });

    it('my first test', function () {
        $this->assertEquals('myPropertyValue', $this->myProperty);
        $this->testName = 'my first test';
    });

    it(function () {
        $this->assertEquals('myStaticPropertyValue', static::$myStaticProperty);
        $this->testName = 'anonymous';
    });

    afterEach(function () {
        echo "testName: {$this->testName}\n";
    });

    afterAll(function () {
        echo 'END';
    });
});
