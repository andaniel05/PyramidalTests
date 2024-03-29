<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit;

require_once __DIR__.'/symbols.php';

use Closure;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\ClassBuilder\TraitBuilder;
use ThenLabs\PyramidalTests\Annotation\Decorator;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\Exception\MacroNotFoundException;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class FluentStyleTest extends UnitTestCase
{
    public function testCreatingTestsInsideATestCase()
    {
        $testCaseModel = testCase($testCaseTitle = uniqid('my test case'))
            ->test('desc1', $this->closure1 = function () {
                $this->assertTrue(true);
            })

            ->test($this->closure2 = function () {
                $this->assertTrue(true);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $class = new ReflectionClass($testCaseModel->getClassBuilder()->getFCQN());

        $this->assertStringContainsString("@testdox {$testCaseTitle}", $class->getDocComment());
    }

    public function testCreatingTestsInsideAnUntitledTestCase()
    {
        testCase()
            ->test('test1', $this->closure1 = function () {
                $this->assertTrue(true);
            })

            ->test($this->closure2 = function () {
                $this->assertTrue(true);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
    }

    public function testNestingTestCases()
    {
        testCase('main test case')
            ->test('the root test 1', $this->closure1 = function () {
                $this->assertTrue(true);
            })

            ->testCase('TestCase1', function () {
                test('the test1', $this->closure2 = function () {
                    $this->assertTrue(true);
                });
            })->end()

            ->test('the root test 2', $this->closure3 = function () {
                $this->assertTrue(true);
            })

            ->testCase()
                ->test('the test2', $this->closure4 = function () {
                    $this->assertTrue(true);
                })
            ->end()

            ->test($this->closure5 = function () {
                $this->assertTrue(true);
            })
        ;

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
        testCase('test case 1')
            ->test($this->closure1 = function () {
                $this->assertTrue(true);
            })

            ->testCase()
                ->test($this->closure2 = function () {
                    $this->assertTrue(true);
                })

                ->testCase('test case 3')
                    ->test('my test', $this->closure3 = function () {
                        $this->assertTrue(true);
                    })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
    }

    public function testSetUpBeforeClass()
    {
        testCase('my test case')
            ->setUpBeforeClass(function () {
                Registry::$data = uniqid();
            })

            ->test('test1', $this->closure1 = function () {
                $this->assertNotEmpty(Registry::$data);
            })

            ->test('test2', $this->closure2 = function () {
                $this->assertNotEmpty(Registry::$data);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
    }

    public function testSetUpBeforeClassInsideNestedTestCases()
    {
        testCase('my test case')
            ->setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => new DateTime];
            })

            ->testCase('nested 1')
                ->setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = new DateTime;
                })

                ->testCase('nested 2')
                    ->setUpBeforeClass(function () {
                        Registry::$data['Nested2'] = new DateTime;
                    })

                    ->test('my test', $this->closure1 = function () {
                        $this->assertInstanceOf(DateTime::class, Registry::$data['MyTestCase']);
                        $this->assertInstanceOf(DateTime::class, Registry::$data['Nested1']);
                        $this->assertInstanceOf(DateTime::class, Registry::$data['Nested2']);

                        $this->assertLessThan(Registry::$data['Nested1'], Registry::$data['MyTestCase']);
                        $this->assertLessThan(Registry::$data['Nested2'], Registry::$data['Nested1']);
                    })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpBeforeClassInsideNestedTestCases1()
    {
        testCase('my test case')
            ->setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => true];
            })

            ->test('my test', $this->closure1 = function () {
                $this->assertTrue(Registry::$data['MyTestCase']);
            })

            ->testCase('nested 1')
                ->setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = true;
                })

                ->test('my test', $this->closure2 = function () {
                    $this->assertTrue(Registry::$data['MyTestCase']);
                    $this->assertTrue(Registry::$data['Nested1']);
                })

                ->testCase('nested 2')
                    ->setUpBeforeClass(function () {
                        Registry::$data['Nested2'] = true;
                    })

                    ->test('my test', $this->closure3 = function () {
                        $this->assertTrue(Registry::$data['MyTestCase']);
                        $this->assertTrue(Registry::$data['Nested1']);
                        $this->assertTrue(Registry::$data['Nested2']);
                    })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
    }

    public function testSetUpBeforeClassInsideNestedTestCasesWithoutInvokeTheParents()
    {
        testCase('my test case')
            ->setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => true];
            })

            ->testCase('nested 1')
                ->setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = true;
                }, false)

                ->test('my test', $this->closure1 = function () {
                    $this->assertArrayNotHasKey('MyTestCase', Registry::$data);
                    $this->assertTrue(Registry::$data['Nested1']);
                })
            ->end()
        ->end();

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

        testCase('my test case 1')
            ->setUp(function () {
                $this->data[] = 'MyTestCase1';
            })

            ->test(function () {
                $this->assertCount(2, $this->data);
                $this->assertEquals('BaseTestCaseClass', $this->data[0]);
                $this->assertEquals('MyTestCase1', $this->data[1]);
            })
        ;

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

        testCase('my test case 1')
            ->setUpBeforeClass(function () {
                Registry::$data['MyTestCase1'] = true;
            })

            ->test('my test', $this->closure1 = function () {
                $this->assertTrue(Registry::$data['MyParentTestCase']);
                $this->assertTrue(Registry::$data['MyTestCase1']);
            })
        ;

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

        testCase('my test case 1')
            ->setUpBeforeClass(function () {
                Registry::$data['MyTestCase1'] = true;
            }, false)

            ->test('my test', $this->closure1 = function () {
                $this->assertArrayNotHasKey('MyParentTestCase', Registry::$data);
                $this->assertTrue(Registry::$data['MyTestCase1']);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpBeforeClassOnce()
    {
        Registry::$data = [];

        testCase('parent test case')
            ->staticProperty('myStaticProperty', 'myStaticPropertyValue')

            ->property('myProperty', 'myPropertyValue')

            ->staticMethod('getMyStaticProperty', function () {
                return static::$myStaticProperty;
            })

            ->method('getMyProperty', function () {
                return $this->myProperty;
            })

            ->setUpBeforeClassOnce(function () {
                Registry::$data[] = 'ParentTestCase';
            })

            ->test($this->closure1 = function () {
                $this->assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
                $this->assertEquals('myPropertyValue', $this->getMyProperty());

                $this->assertCount(1, Registry::$data);
                $this->assertEquals(Registry::$data[0], 'ParentTestCase');
            })

            ->testCase('child test case 1')
                ->setUpBeforeClassOnce(function () {
                    Registry::$data[] = 'ChildTestCase1';
                })

                ->test($this->closure2 = function () {
                    $this->assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
                    $this->assertEquals('myPropertyValue', $this->getMyProperty());

                    $this->assertCount(2, Registry::$data);
                    $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                    $this->assertEquals(Registry::$data[1], 'ChildTestCase1');
                })

                ->testCase('child test case 2')
                    ->setUpBeforeClassOnce(function () {
                        Registry::$data[] = 'ChildTestCase2';
                    })

                    ->test($this->closure3 = function () {
                        $this->assertEquals('myStaticPropertyValue', static::getMyStaticProperty());
                        $this->assertEquals('myPropertyValue', $this->getMyProperty());

                        $this->assertCount(3, Registry::$data);
                        $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                        $this->assertEquals(Registry::$data[1], 'ChildTestCase1');
                        $this->assertEquals(Registry::$data[2], 'ChildTestCase2');
                    })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure3), $result);
    }

    public function testSetUp()
    {
        testCase()
            ->setUp(function () {
                $this->secret = uniqid();
            })

            ->test($this->closure1 = function () {
                $this->assertNotEmpty($this->secret);
            })

            ->test($this->closure2 = function () {
                $this->assertNotEmpty($this->secret);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);
    }

    public function testSetUpInsideNestedTestCases()
    {
        testCase('parent test case')
            ->setUp(function () {
                $this->moment1 = new DateTime;
            })

            ->testCase('nested test case 1')
                ->setUp(function () {
                    $this->moment2 = new DateTime;
                })

                ->testCase('nested test case 2')
                    ->setUp(function () {
                        $this->moment3 = new DateTime;
                    })

                    ->test('my test', $this->closure1 = function () {
                        $this->assertInstanceOf(DateTime::class, $this->moment1);
                        $this->assertInstanceOf(DateTime::class, $this->moment2);
                        $this->assertInstanceOf(DateTime::class, $this->moment3);

                        $this->assertLessThan($this->moment2, $this->moment1);
                        $this->assertLessThan($this->moment3, $this->moment2);
                    })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testSetUpInsideNestedTestCasesWithoutParentInvokation()
    {
        testCase('parent test case')
            ->setUp(function () {
                $this->secret1 = uniqid();
            })

            ->testCase('child test case')
                ->setUp(function () {
                    $this->secret2 = uniqid();
                }, false)

                ->test('my test', $this->closure1 = function () {
                    $this->assertFalse(isset($this->secret1));
                    $this->assertNotEmpty($this->secret2);
                })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testTearDown()
    {
        testCase('my test case')
            ->test('test1', $this->closure1 = function () {
                $this->moment = new DateTime;
                $this->assertTrue(true);
            })

            ->test('test2', $this->closure2 = function () {
                $this->moment = new DateTime;
                $this->assertTrue(true);
            })

            ->tearDown(function () {
                if (! isset(Registry::$data['moments'])) {
                    Registry::$data['moments'] = [];
                }

                Registry::$data['moments'][] = $this->moment;
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $this->assertLessThan(Registry::$data['moments'][1], Registry::$data['moments'][0]);
    }

    public function testTearDownInsideNestedTestCases()
    {
        testCase('my test case')
            ->tearDown(function () {
                Registry::$data = ['parentTearDownMoment' => new DateTime];
            })

            ->testCase('nested 1')
                ->test('test1', $this->closure1 = function () {
                    $this->assertTrue(true);
                })

                ->tearDown(function () {
                    Registry::$data['childTearDownMoment'] = new DateTime;
                })
        ;

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
        testCase('my test case')
            ->tearDown(function () {
                Registry::$data = ['parentTearDown' => true];
            })

            ->testCase('nested 1')
                ->test('test1', $this->closure1 = function () {
                    $this->test1 = true;
                    $this->assertTrue(true);
                })

                ->tearDown(function () {
                    Registry::$data['test1'] = $this->test1;
                    Registry::$data['childTearDown'] = true;
                }, false)
        ;

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

        testCase('my test case 1')
            ->test(function () {
                $this->assertTrue(true);
            })

            ->tearDown(function () {
                Registry::$data[] = 'MyTestCase1';
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertCount(2, Registry::$data);
        $this->assertEquals('BaseTestCaseClass', Registry::$data[0]);
        $this->assertEquals('MyTestCase1', Registry::$data[1]);
    }

    public function testTearDownAfterClass()
    {
        testCase('my test case')
            ->test('test1', $this->closure1 = function () {
                Registry::$data['moment1'] = new DateTime;
                $this->assertTrue(true);
            })

            ->test('test2', $this->closure2 = function () {
                Registry::$data['moment2'] = new DateTime;
                $this->assertTrue(true);
            })

            ->tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass'] = true;
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 2], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $this->assertTrue(Registry::$data['executedTearDownAfterClass']);
        $this->assertLessThan(Registry::$data['moment2'], Registry::$data['moment1']);
    }

    public function testTearDownAfterClassInsideNestedTestCases()
    {
        testCase('parent test case')
            ->tearDownAfterClass(function () {
                Registry::$data['moment1'] = new DateTime;
            })

            ->testCase('my test case')
                ->test('test1', $this->closure1 = function () {
                    $this->assertTrue(true);
                })

                ->tearDownAfterClass(function () {
                    static::assertInstanceOf(
                        DateTime::class,
                        Registry::$data['moment1']
                    );
                    Registry::$data['moment2'] = new DateTime;
                })
        ;

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

        testCase('my test case 1')
            ->test(function () {
                $this->assertTrue(true);
            })

            ->tearDownAfterClass(function () {
                Registry::$data[] = 'MyTestCase1';
            })
        ;

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

        testCase('my test case 1')
            ->test(function () {
                $this->assertTrue(true);
            })

            ->tearDownAfterClass(function () {
                Registry::$data[] = 'MyTestCase1';
            }, false)
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertCount(1, Registry::$data);
        $this->assertEquals('MyTestCase1', Registry::$data[0]);
    }

    public function testTearDownAfterClassInsideNestedTestCasesWithoutMethodInheritance()
    {
        testCase('parent test case')
            ->tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass1'] = true;
            })

            ->testCase('my test case')
                ->test('test1', $this->closure1 = function () {
                    $this->assertTrue(true);
                })

                ->tearDownAfterClass(function () {
                    Registry::$data['executedTearDownAfterClass2'] = true;
                }, false)
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);

        $this->assertFalse(isset(Registry::$data['executedTearDownAfterClass1']));
        $this->assertTrue(Registry::$data['executedTearDownAfterClass2']);
    }

    public function testTearDownAfterClassOnce()
    {
        Registry::$data = [];

        testCase('parent test case')
            ->test(function () {
                $this->assertTrue(true);
            })

            ->tearDownAfterClassOnce(function () {
                Registry::$data[] = 'ParentTestCase';
            })

            ->testCase('child test case 1')
                ->test(function () {
                    $this->assertCount(1, Registry::$data);
                    $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                })

                ->tearDownAfterClassOnce(function () {
                    Registry::$data[] = 'ChildTestCase1';
                })

                ->testCase('child test case 2')
                    ->test(function () {
                        $this->assertCount(2, Registry::$data);
                        $this->assertEquals(Registry::$data[0], 'ParentTestCase');
                        $this->assertEquals(Registry::$data[1], 'ChildTestCase1');
                    })

                    ->tearDownAfterClassOnce(function () {
                        Registry::$data[] = 'ChildTestCase2';
                    })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 3], $result);
    }

    public function testSetTestCaseClass()
    {
        setTestCaseClass(MyCustomTestCase::class);

        $testCaseModel = testCase()
            ->test($this->closure2 = function () {
                $this->assertTrue(true);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure2), $result);

        $testCaseReflectionClass = new ReflectionClass($testCaseModel->getClassBuilder()->getFCQN());

        $this->assertTrue($testCaseReflectionClass->isSubclassOf(MyCustomTestCase::class));
    }

    public function testExtendingTheTestCaseClass()
    {
        $testCaseModel = testCase()
            ->test($this->closure1 = function () {
                $this->assertEquals('myValue', $this->myMethod());
            })
        ;

        $testCaseModel->getClassBuilder()
            ->addMethod('myMethod', function (): string {
                return 'myValue';
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    // public function testEditingTheMethodOfTheTest()
    // {
    //     testCase(function () {
    //         test($this->closure1 = function () {
    //             $this->assertTrue(true);
    //         })->getMethodBuilder()
    //             ->addComment('@group group1')
    //         ;

    //         test(function () {
    //             $this->assertTrue(true);
    //         });
    //     });

    //     $result = $this->runTests(['groups' => ['group1']]);

    //     $this->assertExpectedTotals(['success' => 1], $result);
    //     $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    // }

    // public function testUseMacroThrownsMacroNotFoundException()
    // {
    //     $title = uniqid();

    //     $this->expectException(MacroNotFoundException::class);
    //     $this->expectExceptionMessage("The macro with name '{$title}' is not found.");

    //     useMacro($title);
    // }

    public function testMacro1()
    {
        macro('my global macro', function () {
            test(function () {
                $this->assertTrue(true);
            });
        });

        testCase()
            ->useMacro('my global macro')
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    // public function testMacro2()
    // {
    //     testCase(function () {
    //         macro('my macro', function () {
    //             test(function () {
    //                 $this->assertTrue(true);
    //             });

    //             test(function () {
    //                 $this->assertTrue(true);
    //             });
    //         });

    //         useMacro('my macro');
    //     });

    //     $result = $this->runTests();

    //     $this->assertExpectedTotals(['success' => 2], $result);
    // }

    // public function testMacro3()
    // {
    //     macro('my macro', function () {
    //         test(function () {
    //             $this->assertTrue(true);
    //         });
    //     });

    //     testCase('root test case 1', function () {
    //         macro('my macro', function () {
    //             test(function () {
    //                 $this->assertTrue(true);
    //             });

    //             test(function () {
    //                 $this->assertTrue(true);
    //             });

    //             test(function () {
    //                 $this->assertTrue(true);
    //             });
    //         });

    //         useMacro('my macro');

    //         testCase('child test case 1', function () {
    //             useMacro('my macro');
    //         });
    //     });

    //     testCase('root test case 2', function () {
    //         useMacro('my macro');
    //     });

    //     $result = $this->runTests();

    //     $this->assertExpectedTotals(['success' => 7], $result);
    // }

    // public function testUseAndExtendMacro()
    // {
    //     testCase('root test case 1', function () {
    //         macro('my macro', function () {
    //             test(function () {
    //                 $this->assertTrue(true);
    //             });
    //         });

    //         useAndExtendMacro('my macro', function () {
    //             test(function () {
    //                 $this->assertTrue(true);
    //             });
    //         });
    //     });

    //     $result = $this->runTests();

    //     $this->assertExpectedTotals(['success' => 2], $result);
    // }

    public function testMethodsAndProperties()
    {
        testCase('root test case 1')
            ->staticProperty('myStaticProperty', uniqid('myStaticProperty'))

            ->property('myProperty', uniqid('myProperty'))

            ->staticMethod('myStaticMethod', function () {
                return static::$myStaticProperty;
            })

            ->method('myMethod', function () {
                return $this->myProperty;
            })

            ->test(function () {
                $this->assertStringStartsWith('myProperty', $this->myMethod());
                $this->assertStringStartsWith('myStaticProperty', static::myStaticMethod());
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testMethodsAndPropertiesWithoutInitializationValue()
    {
        testCase('root test case 1')
            ->staticProperty('myStaticProperty')

            ->property('myProperty')

            ->test(function () {
                $this->assertNull($this->myProperty);
                $this->assertNull(static::$myStaticProperty);
            })
        ;

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

        testCase('root test case 1')
            ->useTrait($trait->getFCQN(), ['method2 as method10'])

            ->test($this->closure1 = function () {
                $this->assertEquals('method1', $this->method1());
                $this->assertEquals('method2', $this->method2());
                $this->assertEquals('method2', $this->method10());
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($this->closure1), $result);
    }

    public function testExceptionWhenMissingDecorator()
    {
        $this->expectException(Exception::class);
        // $this->expectExceptionMessage("Decorator 'decorator1' for class 'PHPUnit\Framework\TestCase' is missing.");

        testCase()->decorator1();
    }

    public function testDecoratorForClass()
    {
        $classBuilder = (new ClassBuilder())->extends(TestCase::class);
        $classBuilder->install();

        DecoratorsRegistry::register(
            $classBuilder->getFCQN(),
            'myDecorator',
            new class extends AbstractDecorator {
                public function applyTo(TestCaseModel $testCaseModel, array $arguments)
                {
                    $name = $arguments[0];
                    $value = $arguments[1];

                    $baseClassBuilder = $testCaseModel->getBaseClassBuilder();

                    $baseClassBuilder->addProperty($name)->setValue($value);
                }
            }
        );

        setTestCaseClass($classBuilder->getFCQN());

        testCase()
            ->myDecorator('myProperty', true)

            ->test(function () {
                // myProperty is created from the decorator.
                $this->assertTrue($this->myProperty);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testDecoratorForClass1()
    {
        DecoratorsRegistry::register(
            uniqid('UnexistentClass'),
            'myDecorator',
            new class extends AbstractDecorator {
                public function applyTo(TestCaseModel $testCaseModel, array $arguments)
                {
                }
            }
        );

        $classBuilder = (new ClassBuilder())->extends(TestCase::class);
        $classBuilder->install();

        DecoratorsRegistry::register(
            $classBuilder->getFCQN(),
            'myDecorator',
            new class extends AbstractDecorator {
                public function applyTo(TestCaseModel $testCaseModel, array $arguments)
                {
                    $name = $arguments[0];
                    $value = $arguments[1];

                    $baseClassBuilder = $testCaseModel->getBaseClassBuilder();

                    $baseClassBuilder->addProperty($name)->setValue($value);
                }
            }
        );

        setTestCaseClass($classBuilder->getFCQN());

        testCase()
            ->myDecorator('myProperty', true)

            ->test(function () {
                // myProperty is created from the decorator.
                $this->assertTrue($this->myProperty);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testDecoratorWhichReturnsSetUpBeforeClassDecorator()
    {
        $classBuilder = (new ClassBuilder())->extends(TestCase::class);
        $classBuilder->install();

        DecoratorsRegistry::register(
            $classBuilder->getFCQN(),
            'customDecorator',
            new class extends AbstractDecorator {
                public function getClosure(array $arguments): ?Closure
                {
                    return function () {
                        static::$myProperty = 10;
                    };
                }

                public function applyTo(TestCaseModel $testCaseModel, array $arguments)
                {
                    $classBuilder = $testCaseModel->getClassBuilder();
                    $classBuilder->addProperty($arguments[0])->setStatic(true);
                }
            }
        );

        setTestCaseClass($classBuilder->getFCQN());

        testCase()
            ->customDecorator('myProperty')

            ->test(function () {
                // the property value is assigned by the decorator.
                $this->assertEquals(10, static::$myProperty);
            })->getParent()
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testDecoratorsFromMethodsWithDecoratorAnnotation()
    {
        setTestCaseClass(MyDecoratorTestCase::class);

        testCase()
            ->customDecorator('myProperty')

            ->test(function () {
                // the property value is assigned by the decorator.
                $this->assertEquals(10, static::$myProperty);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }

    public function testDecoratorsFromTheDeclaredTraitsByTheClassBuilder()
    {
        $trait = (new TraitBuilder)
            ->addMethod('method1')
                ->addComment('@ThenLabs\PyramidalTests\Annotation\Decorator(name="customDecorator")')
                ->setStatic(true)
                ->setClosure(function () {
                    return new class extends AbstractDecorator {
                        public function getClosure(array $arguments): ?Closure
                        {
                            return function () {
                                static::$myProperty = 50;
                            };
                        }

                        public function applyTo(TestCaseModel $testCaseModel, array $arguments)
                        {
                            $classBuilder = $testCaseModel->getClassBuilder();
                            $classBuilder->addProperty($arguments[0])->setStatic(true);
                        }
                    };
                })
                ->end()
        ->install();

        testCase()
            ->importDecorators($trait->getFCQN())
            ->customDecorator('myProperty')
            ->test(function () {
                // the property value is assigned by the decorator.
                $this->assertEquals(50, static::$myProperty);
            })
        ;

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
    }
}
