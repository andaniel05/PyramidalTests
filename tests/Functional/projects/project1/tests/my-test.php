<?php

testCase('my parent test case', function () {
    test('my first test', function () {
        $this->assertTrue(true);
    });

    test('my test 2', function () {
        $this->assertTrue(true);
    });

    test('my test 3', function () {
        $this->assertTrue(true);
    });

    testCase('my child test case', function () {
        test(function () {
            $this->assertTrue(true);
        });

        test(function () {
            $this->assertTrue(true);
        });

        testCase(function () {
            test('my end test', function () {
                $this->assertTrue(true);
            });
        });
    });
});
