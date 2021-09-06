<?php

class Aux
{
    public static $data = [];
}

class MyTestCase extends \PHPUnit\Framework\TestCase
{
    public static function auxSetUpBeforeClass(): void
    {
        Aux::$data[] = __METHOD__;
    }

    public function auxSetUp(): void
    {
        Aux::$data[] = __METHOD__;
    }

    public function auxTest(): void
    {
        Aux::$data[] = __METHOD__;
    }

    public static function auxTearDownAfterClass(): void
    {
        Aux::$data[] = __METHOD__;
    }

    public function auxTearDown(): void
    {
        Aux::$data[] = __METHOD__;
    }
}

setTestCaseClass('MyTestCase');

testCase(function () {
    setUpBeforeClass(function () {
        static::auxSetUpBeforeClass();
    });

    setUp(function () {
        $this->auxSetUp();
    });

    test(function () {
        $this->auxTest();
        $this->assertTrue(true);
    });

    tearDown(function () {
        $this->auxTearDown();
    });

    tearDownAfterClass(function () {
        static::auxTearDownAfterClass();
    });

    testCase(function () {
        setUpBeforeClass(function () {
            static::auxSetUpBeforeClass();
        });

        setUp(function () {
            $this->auxSetUp();
        });

        test(function () {
            $this->auxTest();
            $this->assertTrue(true);
        });

        tearDown(function () {
            $this->auxTearDown();
        });

        tearDownAfterClass(function () {
            static::auxTearDownAfterClass();
        });

        testCase(function () {
            setUpBeforeClass(function () {
                static::auxSetUpBeforeClass();
            });

            setUp(function () {
                $this->auxSetUp();
            });

            test(function () {
                $this->auxTest();
                $this->assertTrue(true);
            });

            tearDown(function () {
                $this->auxTearDown();
            });

            tearDownAfterClass(function () {
                static::auxTearDownAfterClass();
            });
        });
    });
});

test(function () {
    $this->assertContains('MyTestCase::auxSetUpBeforeClass', Aux::$data);
    $this->assertContains('MyTestCase::auxSetUp', Aux::$data);
    $this->assertContains('MyTestCase::auxTest', Aux::$data);
    $this->assertContains('MyTestCase::auxTearDownAfterClass', Aux::$data);
    $this->assertContains('MyTestCase::auxTearDown', Aux::$data);
});