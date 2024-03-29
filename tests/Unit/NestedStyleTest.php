<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit;

use DateTime;
use ReflectionClass;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\ClassBuilder\TraitBuilder;
use ThenLabs\PyramidalTests\Exception\MacroNotFoundException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class NestedStyleTest extends UnitTestCase
{
    public function testCreatingTestsInsideATestCase()
    {
        testCase($testCaseTitle = uniqid('my test case'), $closure = function () {
            test('desc1', $this->closure1 = function () {
                $this->assertTrue(true);
            });

            test($this->closure2 = function () {
                $this->assertTrue(true);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $testCaseModel = $this->getTestCaseModelFromClosure($closure);
        $class = new ReflectionClass($testCaseModel->getClassBuilder()->getFCQN());

        $this->assertStringContainsString("@testdox {$testCaseTitle}", $class->getDocComment());
    }

    public function testCreatingTestsInsideAnUntitledTestCase()
    {
        testCase($testCase = function () {
            test('test1', $this->closure1 = function () {
                $this->assertTrue(true);
            });

            test($this->closure2 = function () {
                $this->assertTrue(true);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
    }

    public function testNestingTestCases()
    {
        testCase('main test case', function () {
            test('the root test 1', $this->closure1 = function () {
                $this->assertTrue(true);
            });

            testCase('TestCase1', function () {
                test('the test1', $this->closure2 = function () {
                    $this->assertTrue(true);
                });
            });

            test('the root test 2', $this->closure3 = function () {
                $this->assertTrue(true);
            });

            testCase(function () {
                test('the test2', $this->closure4 = function () {
                    $this->assertTrue(true);
                });
            });

            test($this->closure5 = function () {
                $this->assertTrue(true);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 5], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure4), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure5), $result);
    }

    public function testNestingSeveralTestCases()
    {
        testCase('test case 1', function () {
            test($this->closure1 = function () {
                $this->assertTrue(true);
            });

            testCase(function () {
                test($this->closure2 = function () {
                    $this->assertTrue(true);
                });

                testCase('test case 3', function () {
                    test('my test', $this->closure3 = function () {
                        $this->assertTrue(true);
                    });
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
    }

    public function testSetUpBeforeClass()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data = uniqid();
            });

            test('test1', $this->closure1 = function () {
                $this->assertNotEmpty(Registry::$data);
            });

            test('test2', $this->closure2 = function () {
                $this->assertNotEmpty(Registry::$data);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
    }

    public function testSetUpBeforeClassInsideNestedTestCases()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => new DateTime];
            });

            testCase('nested 1', function () {
                setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = new DateTime;
                });

                testCase('nested 2', function () {
                    setUpBeforeClass(function () {
                        Registry::$data['Nested2'] = new DateTime;
                    });

                    test('my test', $this->closure1 = function () {
                        $this->assertInstanceOf(DateTime::class, Registry::$data['MyTestCase']);
                        $this->assertInstanceOf(DateTime::class, Registry::$data['Nested1']);
                        $this->assertInstanceOf(DateTime::class, Registry::$data['Nested2']);

                        $this->assertLessThan(Registry::$data['Nested1'], Registry::$data['MyTestCase']);
                        $this->assertLessThan(Registry::$data['Nested2'], Registry::$data['Nested1']);
                    });
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpBeforeClassInsideNestedTestCases1()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => true];
            });

            test('my test', $this->closure1 = function () {
                $this->assertTrue(Registry::$data['MyTestCase']);
            });

            testCase('nested 1', function () {
                setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = true;
                });

                test('my test', $this->closure2 = function () {
                    $this->assertTrue(Registry::$data['MyTestCase']);
                    $this->assertTrue(Registry::$data['Nested1']);
                });

                testCase('nested 2', function () {
                    setUpBeforeClass(function () {
                        Registry::$data['Nested2'] = true;
                    });

                    test('my test', $this->closure3 = function () {
                        $this->assertTrue(Registry::$data['MyTestCase']);
                        $this->assertTrue(Registry::$data['Nested1']);
                        $this->assertTrue(Registry::$data['Nested2']);
                    });
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
    }

    public function testSetUpBeforeClassInsideNestedTestCasesWithoutInvokeTheParents()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => true];
            });

            testCase('nested 1', function () {
                setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = true;
                }, false);

                test('my test', $this->closure1 = function () {
                    $this->assertArrayNotHasKey('MyTestCase', Registry::$data);
                    $this->assertTrue(Registry::$data['Nested1']);
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpInvokeParentMethodOfTheBaseClass()
    {
        $baseTestCaseClass = (new ClassBuilder)
            ->extends('PHPUnit\Framework\TestCase')
            ->addMethod('setUp')
                ->setClosure(function (): void {
                    $this->data = ['BaseTestCaseClass'];
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case 1', function () {
            setUp(function () {
                $this->data[] = 'MyTestCase1';
            });

            test(function () {
                $this->assertCount(2, $this->data);
                $this->assertEquals('BaseTestCaseClass', $this->data[0]);
                $this->assertEquals('MyTestCase1', $this->data[1]);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testSetUpBeforeClassInvokeTheParentMethodOfTheTestCaseClass()
    {
        $baseTestCaseClass = (new ClassBuilder)
            ->extends('PHPUnit\Framework\TestCase')
            ->addMethod('setUpBeforeClass')
                ->setStatic(true)
                ->setClosure(function (): void {
                    Registry::$data = ['MyParentTestCase' => true];
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case 1', function () {
            setUpBeforeClass(function () {
                Registry::$data['MyTestCase1'] = true;
            });

            test('my test', $this->closure1 = function () {
                $this->assertTrue(Registry::$data['MyParentTestCase']);
                $this->assertTrue(Registry::$data['MyTestCase1']);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpBeforeClassInvokeTheParentMethodOfTheTestCaseClass1()
    {
        $baseTestCaseClass = (new ClassBuilder)
            ->extends('PHPUnit\Framework\TestCase')
            ->addMethod('setUpBeforeClass')
                ->setStatic(true)
                ->setClosure(function (): void {
                    Registry::$data = ['MyParentTestCase' => true];
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case 1', function () {
            setUpBeforeClass(function () {
                Registry::$data['MyTestCase1'] = true;
            }, false);

            test('my test', $this->closure1 = function () {
                $this->assertArrayNotHasKey('MyParentTestCase', Registry::$data);
                $this->assertTrue(Registry::$data['MyTestCase1']);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpBeforeClassOnce()
    {
        Registry::$data = [];

        testCase('parent test case', function () {
            staticProperty('myStaticProperty', 'myStaticPropertyValue');
            property('myProperty', 'myPropertyValue');

            staticMethod('getMyStaticProperty', function () {
                return static::$myStaticProperty;
            });

            method('getMyProperty', function () {
                return $this->myProperty;
            });

            setUpBeforeClassOnce(function () {
                Registry::$data[] = 'ParentTestCase';
            });

            test($this->closure1 = function () {
                $this->assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
                $this->assertEquals('myPropertyValue', $this->getMyProperty());

                $this->assertCount(1, Registry::$data);
                $this->assertEquals(Registry::$data[0], 'ParentTestCase');
            });

            testCase('child test case 1', function () {
                setUpBeforeClassOnce(function () {
                    Registry::$data[] = 'ChildTestCase1';
                });

                test($this->closure2 = function () {
                    $this->assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
                    $this->assertEquals('myPropertyValue', $this->getMyProperty());

                    $this->assertCount(2, Registry::$data);
                    $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                    $this->assertEquals(Registry::$data[1], 'ChildTestCase1');
                });

                testCase('child test case 2', function () {
                    setUpBeforeClassOnce(function () {
                        Registry::$data[] = 'ChildTestCase2';
                    });

                    test($this->closure3 = function () {
                        $this->assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
                        $this->assertEquals('myPropertyValue', $this->getMyProperty());

                        $this->assertCount(3, Registry::$data);
                        $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                        $this->assertEquals(Registry::$data[1], 'ChildTestCase1');
                        $this->assertEquals(Registry::$data[2], 'ChildTestCase2');
                    });
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
    }

    public function testSetUp()
    {
        testCase(function () {
            setUp(function () {
                $this->secret = uniqid();
            });

            test($this->closure1 = function () {
                $this->assertNotEmpty($this->secret);
            });

            test($this->closure2 = function () {
                $this->assertNotEmpty($this->secret);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
    }

    public function testSetUpInsideNestedTestCases()
    {
        testCase('parent test case', function () {
            setUp(function () {
                $this->moment1 = new DateTime;
            });

            testCase('nested test case 1', function () {
                setUp(function () {
                    $this->moment2 = new DateTime;
                });

                testCase('nested test case 2', function () {
                    setUp(function () {
                        $this->moment3 = new DateTime;
                    });

                    test('my test', $this->closure1 = function () {
                        $this->assertInstanceOf(DateTime::class, $this->moment1);
                        $this->assertInstanceOf(DateTime::class, $this->moment2);
                        $this->assertInstanceOf(DateTime::class, $this->moment3);

                        $this->assertLessThan($this->moment2, $this->moment1);
                        $this->assertLessThan($this->moment3, $this->moment2);
                    });
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpInsideNestedTestCasesWithoutParentInvokation()
    {
        testCase('parent test case', function () {
            setUp(function () {
                $this->secret1 = uniqid();
            });

            testCase('child test case', function () {
                setUp(function () {
                    $this->secret2 = uniqid();
                }, false);

                test('my test', $this->closure1 = function () {
                    $this->assertFalse(isset($this->secret1));
                    $this->assertNotEmpty($this->secret2);
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testTearDown()
    {
        testCase('my test case', function () {
            test('test1', $this->closure1 = function () {
                $this->moment = new DateTime;
                $this->assertTrue(true);
            });

            test('test2', $this->closure2 = function () {
                $this->moment = new DateTime;
                $this->assertTrue(true);
            });

            tearDown(function () {
                if (! isset(Registry::$data['moments'])) {
                    Registry::$data['moments'] = [];
                }

                Registry::$data['moments'][] = $this->moment;
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $this->assertLessThan(Registry::$data['moments'][1], Registry::$data['moments'][0]);
    }

    public function testTearDownInsideNestedTestCases()
    {
        testCase('my test case', function () {
            tearDown(function () {
                Registry::$data = ['parentTearDownMoment' => new DateTime];
            });

            testCase('nested 1', function () {
                test('test1', $this->closure1 = function () {
                    $this->assertTrue(true);
                });

                tearDown(function () {
                    Registry::$data['childTearDownMoment'] = new DateTime;
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);

        $this->assertLessThan(
            Registry::$data['childTearDownMoment'],
            Registry::$data['parentTearDownMoment']
        );
    }

    public function testTearDownInsideNestedTestCasesWithoutMethodInheritance()
    {
        testCase('my test case', function () {
            tearDown(function () {
                Registry::$data = ['parentTearDown' => true];
            });

            testCase('nested 1', function () {
                test('test1', $this->closure1 = function () {
                    $this->test1 = true;
                    $this->assertTrue(true);
                });

                tearDown(function () {
                    Registry::$data['test1'] = $this->test1;
                    Registry::$data['childTearDown'] = true;
                }, false);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);

        $this->assertTrue(Registry::$data['test1']);
        $this->assertTrue(Registry::$data['childTearDown']);
        $this->assertArrayNotHasKey('parentTearDown', Registry::$data);
    }

    public function testTearDownInvokeParentMethodOfTheBaseClass()
    {
        $baseTestCaseClass = (new ClassBuilder)
            ->extends('PHPUnit\Framework\TestCase')
            ->addMethod('tearDown')
                ->setClosure(function (): void {
                    Registry::$data = ['BaseTestCaseClass'];
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case 1', function () {
            test(function () {
                $this->assertTrue(true);
            });

            tearDown(function () {
                Registry::$data[] = 'MyTestCase1';
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertCount(2, Registry::$data);
        $this->assertEquals('BaseTestCaseClass', Registry::$data[0]);
        $this->assertEquals('MyTestCase1', Registry::$data[1]);
    }

    public function testTearDownAfterClass()
    {
        testCase('my test case', function () {
            test('test1', $this->closure1 = function () {
                Registry::$data['moment1'] = new DateTime;
                $this->assertTrue(true);
            });

            test('test2', $this->closure2 = function () {
                Registry::$data['moment2'] = new DateTime;
                $this->assertTrue(true);
            });

            tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass'] = true;
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $this->assertTrue(Registry::$data['executedTearDownAfterClass']);
        $this->assertLessThan(Registry::$data['moment2'], Registry::$data['moment1']);
    }

    public function testTearDownAfterClassInsideNestedTestCases()
    {
        testCase('parent test case', function () {
            tearDownAfterClass(function () {
                Registry::$data['moment1'] = new DateTime;
            });

            testCase('my test case', function () {
                test('test1', $this->closure1 = function () {
                    $this->assertTrue(true);
                });

                tearDownAfterClass(function () {
                    static::assertInstanceOf(
                        DateTime::class,
                        Registry::$data['moment1']
                    );
                    Registry::$data['moment2'] = new DateTime;
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);

        $this->assertLessThan(Registry::$data['moment2'], Registry::$data['moment1']);
    }

    public function testTearDownAfterClassInvokeParentMethodOfTheBaseClass()
    {
        $baseTestCaseClass = (new ClassBuilder)
            ->extends('PHPUnit\Framework\TestCase')
            ->addMethod('tearDownAfterClass')
                ->setStatic(true)
                ->setClosure(function (): void {
                    Registry::$data[] = 'MyParentClass';
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case 1', function () {
            test(function () {
                $this->assertTrue(true);
            });

            tearDownAfterClass(function () {
                Registry::$data[] = 'MyTestCase1';
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertEquals('MyParentClass', Registry::$data[0]);
        $this->assertEquals('MyTestCase1', Registry::$data[1]);
    }

    public function testTearDownAfterClassInvokeParentMethodOfTheBaseClass1()
    {
        $baseTestCaseClass = (new ClassBuilder)
            ->extends('PHPUnit\Framework\TestCase')
            ->addMethod('tearDownAfterClass')
                ->setStatic(true)
                ->setClosure(function (): void {
                    Registry::$data[] = 'MyParentClass';
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case 1', function () {
            test(function () {
                $this->assertTrue(true);
            });

            tearDownAfterClass(function () {
                Registry::$data[] = 'MyTestCase1';
            }, false);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertCount(1, Registry::$data);
        $this->assertEquals('MyTestCase1', Registry::$data[0]);
    }

    public function testTearDownAfterClassInsideNestedTestCasesWithoutMethodInheritance()
    {
        testCase('parent test case', function () {
            tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass1'] = true;
            });

            testCase('my test case', function () {
                test('test1', $this->closure1 = function () {
                    $this->assertTrue(true);
                });

                tearDownAfterClass(function () {
                    Registry::$data['executedTearDownAfterClass2'] = true;
                }, false);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);

        $this->assertFalse(isset(Registry::$data['executedTearDownAfterClass1']));
        $this->assertTrue(Registry::$data['executedTearDownAfterClass2']);
    }

    public function testTearDownAfterClassOnce()
    {
        Registry::$data = [];

        testCase('parent test case', function () {
            test(function () {
                $this->assertTrue(true);
            });

            tearDownAfterClassOnce(function () {
                Registry::$data[] = 'ParentTestCase';
            });

            testCase('child test case 1', function () {
                test(function () {
                    $this->assertCount(1, Registry::$data);
                    $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                });

                tearDownAfterClassOnce(function () {
                    Registry::$data[] = 'ChildTestCase1';
                });

                testCase('child test case 2', function () {
                    test(function () {
                        $this->assertCount(2, Registry::$data);
                        $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                        $this->assertEquals(Registry::$data[1], 'ChildTestCase1');
                    });

                    tearDownAfterClassOnce(function () {
                        Registry::$data[] = 'ChildTestCase2';
                    });
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
    }

    public function testSetTestCaseClass()
    {
        setTestCaseClass(MyCustomTestCase::class);

        testCase($this->closure1 = function () {
            test($this->closure2 = function () {
                $this->assertTrue(true);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $testCaseModel = $this->getTestCaseModelFromClosure($this->closure1);
        $testCaseReflectionClass = new ReflectionClass($testCaseModel->getClassBuilder()->getFCQN());

        $this->assertTrue($testCaseReflectionClass->isSubclassOf(MyCustomTestCase::class));
    }

    public function testExtendingTheTestCaseClass()
    {
        testCase(function () {
            test($this->closure1 = function () {
                $this->assertEquals('myValue', $this->myMethod());
            });
        })->getClassBuilder()
            ->addMethod('myMethod', function (): string {
                return 'myValue';
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testEditingTheMethodOfTheTest()
    {
        testCase(function () {
            test($this->closure1 = function () {
                $this->assertTrue(true);
            })->getMethodBuilder()
                ->addComment('@group group1')
            ;

            test(function () {
                $this->assertTrue(true);
            });
        });

        $result = $this->runTests(['groups' => ['group1']]);

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testUseMacroThrownsMacroNotFoundException()
    {
        $title = uniqid();

        $this->expectException(MacroNotFoundException::class);
        $this->expectExceptionMessage("The macro with name '{$title}' is not found.");

        useMacro($title);
    }

    public function testMacro1()
    {
        macro('my global macro', function () {
            test(function () {
                $this->assertTrue(true);
            });
        });

        testCase(function () {
            useMacro('my global macro');
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testMacro2()
    {
        testCase(function () {
            macro('my macro', function () {
                test(function () {
                    $this->assertTrue(true);
                });

                test(function () {
                    $this->assertTrue(true);
                });
            });

            useMacro('my macro');
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
    }

    public function testMacro3()
    {
        macro('my macro', function () {
            test(function () {
                $this->assertTrue(true);
            });
        });

        testCase('root test case 1', function () {
            macro('my macro', function () {
                test(function () {
                    $this->assertTrue(true);
                });

                test(function () {
                    $this->assertTrue(true);
                });

                test(function () {
                    $this->assertTrue(true);
                });
            });

            useMacro('my macro');

            testCase('child test case 1', function () {
                useMacro('my macro');
            });
        });

        testCase('root test case 2', function () {
            useMacro('my macro');
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 7], $result);
    }

    public function testUseAndExtendMacro()
    {
        testCase('root test case 1', function () {
            macro('my macro', function () {
                test(function () {
                    $this->assertTrue(true);
                });
            });

            useAndExtendMacro('my macro', function () {
                test(function () {
                    $this->assertTrue(true);
                });
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
    }

    public function testMethodsAndProperties()
    {
        testCase('root test case 1', function () {
            staticProperty(
                'myStaticProperty',
                uniqid('myStaticProperty')
            );

            property(
                'myProperty',
                uniqid('myProperty')
            );

            staticMethod('myStaticMethod', function () {
                return static::$myStaticProperty;
            });

            method('myMethod', function () {
                return $this->myProperty;
            });

            test(function () {
                $this->assertStringStartsWith('myProperty', $this->myMethod());
                $this->assertStringStartsWith('myStaticProperty', static::myStaticMethod());
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testMethodsAndPropertiesWithoutInitializationValue()
    {
        testCase('root test case 1', function () {
            staticProperty('myStaticProperty');

            property('myProperty');

            test(function () {
                $this->assertNull($this->myProperty);
                $this->assertNull(static::$myStaticProperty);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testUseTrait()
    {
        $trait = (new TraitBuilder)
            ->addMethod('method1', function () {
                return 'method1';
            })->end()
            ->addMethod('method2', function () {
                return 'method2';
            })->end()
        ;
        $trait->install();

        testCase('root test case 1', function () use ($trait) {
            useTrait($trait->getFCQN(), ['method2 as method10']);

            test($this->closure1 = function () {
                $this->assertEquals('method1', $this->method1());
                $this->assertEquals('method2', $this->method2());
                $this->assertEquals('method2', $this->method10());
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testUsingProviderAcrossTheWithMethod1()
    {
        test(function (int $v1, int $v2, int $result) {
            $this->assertEquals($result, $v1 + $v2);
        })->with([
            [1, 1, 2],
            [2, 2, 4],
            [3, 3, 6],
        ]);

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
    }

    public function testUsingProviderAcrossTheWithMethod2()
    {
        testCase(function () {
            test(function (int $v1, int $v2, int $result) {
                $this->assertEquals($result, $v1 + $v2);
            })->with([
                [1, 1, 2],
                [2, 2, 4],
                [3, 3, 6],
            ]);

            test(function (int $v1, int $v2, int $result) {
                $this->assertEquals($result, $v1 + $v2);
            })->with([
                [1, 1, 2],
            ]);

            testCase(function () {
                test(function (int $v1, int $v2, int $result) {
                    $this->assertEquals($result, $v1 + $v2);
                })->with([
                    [1, 1, 2],
                ]);
            });
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 5], $result);
    }

    public function testCreateARootTestWithoutTitleOrClosure()
    {
        test()->assertSame(50, 50);

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateARootTestWithoutAClosure()
    {
        test('a test without closure')->assertNull(null);

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateARootTestWithoutAClosureWithSeveralDecorators()
    {
        test('a test without closure')
            ->assertTrue(true)
            ->assertNull(null)
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestWithoutTitleOrClosure()
    {
        testCase(function () {
            test()->assertSame(50, 50);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestWithoutAClosure()
    {
        testCase(function () {
            test('a test without closure')->assertNull(null);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestWithoutAClosureWithSeveralDecorators()
    {
        testCase(function () {
            test('a test without closure')
                ->assertTrue(true)
                ->assertNull(null);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateARootTestWithItAndWithoutTitleOrClosure()
    {
        it()->assertSame(50, 50);

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateARootTestWithItAndWithoutAClosure()
    {
        it('a test without closure')->assertNull(null);

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateARootTestWithItAndWithoutAClosureWithSeveralDecorators()
    {
        test('a test without closure')
            ->assertTrue(true)
            ->assertNull(null)
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestWithItAndWithoutTitleOrClosure()
    {
        testCase(function () {
            it()->assertSame(50, 50);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestWithItAndWithoutAClosure()
    {
        testCase(function () {
            it('a test without closure')->assertNull(null);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestWithItAndWithoutAClosureWithSeveralDecorators()
    {
        testCase(function () {
            it('a test without closure')
                ->assertTrue(true)
                ->assertNull(null);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestComposedByMultilevelDecoratorsUsingEndForGoTheTop()
    {
        $object = new class {
            public function objectMethod1(): self
            {
                echo 'objectMethod1'.PHP_EOL;

                return $this;
            }

            public function objectMethod2(): self
            {
                echo 'objectMethod2'.PHP_EOL;

                return $this;
            }
        };

        $baseTestCaseClass = (new ClassBuilder)->extends('PHPUnit\Framework\TestCase')
            ->addMethod('myMethod')
                ->setClosure(function () use ($object) {
                    return $object;
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        test()
            ->myMethod()
                ->objectMethod1()
                ->objectMethod2()
            ->end()
            ->expectOutputString("objectMethod1\nobjectMethod2\n")
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestComposedByMultilevelDecoratorsUsingEndForGoTheTop1()
    {
        $object = new class {
            public function objectMethod1()
            {
                echo 'objectMethod1'.PHP_EOL;

                return new class {
                    public function objectMethod2()
                    {
                        echo 'objectMethod2'.PHP_EOL;
                    }
                };
            }
        };

        $baseTestCaseClass = (new ClassBuilder)->extends('PHPUnit\Framework\TestCase')
            ->addMethod('myMethod')
                ->setClosure(function () use ($object) {
                    return $object;
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        test()
            ->myMethod()
            ->objectMethod1()
            ->objectMethod2()
            ->expectOutputString("objectMethod1\nobjectMethod2\n")
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testCreateATestComposedByMultilevelDecoratorsWithoutUsingEnd()
    {
        $object = new class {
            public function objectMethod1(): self
            {
                echo 'objectMethod1'.PHP_EOL;

                return $this;
            }

            public function objectMethod2(): self
            {
                echo 'objectMethod2'.PHP_EOL;

                return $this;
            }
        };

        $baseTestCaseClass = (new ClassBuilder)->extends('PHPUnit\Framework\TestCase')
            ->addMethod('myMethod')
                ->setClosure(function () use ($object) {
                    return $object;
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        test()
            ->myMethod()
            ->objectMethod1()
            ->objectMethod2()
            ->expectOutputString("objectMethod1\nobjectMethod2\n")
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testDecoratedTestCaseCanInvokeStaticMethodsOfTestCaseClass()
    {
        $baseTestCaseClass = (new ClassBuilder)->extends('PHPUnit\Framework\TestCase')
            ->addProperty('counter')
                ->setStatic(true)
                ->setDefaultValue(0)
            ->end()

            ->addMethod('myStaticWichReturnsNone')
                ->setStatic(true)
                ->setClosure(function () {
                    static::$counter++;
                })
            ->end()

            ->addMethod('myStaticMethodWichReturnsAnObject')
                ->setStatic(true)
                ->setClosure(function () {
                    static::$counter++;
                })
            ->end()
        ;
        $baseTestCaseClass->install();

        setTestCaseClass($baseTestCaseClass->getFCQN());

        testCase('my test case')
            ->myStaticWichReturnsNone()
            ->myStaticMethodWichReturnsAnObject()
            ->test(function () {
                $this->assertSame(2, static::$counter);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }
}
