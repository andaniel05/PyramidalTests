<?php
declare(strict_types=1);

/*
    Copyright (C) <2018>  <Andy Daniel Navarro Taño>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Andaniel05\PyramidalTests\Tests;

use Andaniel05\PyramidalTests\Extension;
use Andaniel05\PyramidalTests\Model\Record;
use Andaniel05\PyramidalTests\Exception\DuplicatedTestException;
use Andaniel05\PyramidalTests\Exception\InvalidContextException;
use Andaniel05\PyramidalTests\Exception\InvalidMethodNameException;
use Andaniel05\PyramidalTests\Exception\InvalidTestCaseClassException;
use Andaniel05\PyramidalTests\Exception\MacroNotFoundException;
use Andaniel05\PyramidalTests\Tests\Utils\Registry;
use Andaniel05\PyramidalTests\Tests\Dummies\Observer;
use Andaniel05\PyramidalTests\Tests\Dummies\SomeClass;
use Andaniel05\PyramidalTests\Tests\Dummies\Subject;
use Andaniel05\PyramidalTests\Tests\Dummies\TestCase as DummyTestCase;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ExtensionTest extends BaseTestCase
{
    public function testCreatingATestInTheDefaultTestCase()
    {
        test('the test1', function () {
            $this->assertTrue(true);
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\DefaultTestCase::testTheTest1",
            $result
        );
    }

    public function testCreatingAnAnonymousTestInTheDefaultTestCase()
    {
        test($test = function () {
            $this->assertTrue(true);
        });

        $result = Extension::run();

        $testName = Record::getTestNameByClosure($test);
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\DefaultTestCase::{$testName}",
            $result
        );
    }

    public function testStubsSupport()
    {
        test('stubs support', function () {
            $stub = $this->createMock(SomeClass::class);

            $stub->method('doSomething')
                 ->willReturn('foo');

            $this->assertSame('foo', $stub->doSomething());
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\DefaultTestCase::testStubsSupport",
            $result
        );
    }

    public function testMocksSupport()
    {
        test('mocks support', function () {
            $observer = $this->getMockBuilder(Observer::class)
                             ->setMethods(['update'])
                             ->getMock();

            $observer->expects($this->once())
                     ->method('update')
                     ->with($this->equalTo('something'));

            $subject = new Subject('My subject');
            $subject->attach($observer);

            $subject->doSomething();
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\DefaultTestCase::testMocksSupport",
            $result
        );
    }

    public function testCreatingTestsInsideATestCase()
    {
        testCase('my test case', function () {
            test('desc1', function () {
                $this->assertTrue(true);
            });

            test($this->anonymousTest = function () {
                $this->assertTrue(true);
            });
        });

        $result = Extension::run();

        $anonymousTestName = Record::getTestNameByClosure($this->anonymousTest);

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase::testDesc1",
            $result
        );
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase::{$anonymousTestName}",
            $result
        );
    }

    public function testCreatingTestsInsideAnAnonymousTestCase()
    {
        testCase($testCase = function () {
            test('test1', function () {
                $this->assertTrue(true);
            });

            test($this->anonymousTest = function () {
                $this->assertTrue(true);
            });
        });

        $result = Extension::run();

        $testCaseName = Record::getTestCaseNameByClosure($testCase);
        $anonymousTestName = Record::getTestNameByClosure($this->anonymousTest);

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\{$testCaseName}::testTest1",
            $result
        );
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\{$testCaseName}::{$anonymousTestName}",
            $result
        );
    }

    public function testFilteringATestCase()
    {
        testCase('test case 1', function () {
            test($this->test1 = function () {
                $this->assertTrue(true);
            });
        });

        testCase('test case 2', function () {
            test($this->test2 = function () {
                $this->assertTrue(true);
            });
        });

        $result = Extension::run(['filter' => 'TestCase1']);

        $testName1 = Record::getTestNameByClosure($this->test1);
        $testName2 = Record::getTestNameByClosure($this->test2);

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\TestCase1::{$testName1}",
            $result
        );
        $this->assertTestWasNotExecuted(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\TestCase2::{$testName2}",
            $result
        );
    }

    public function testFilteringAnonymouses()
    {
        $registry = [];

        testCase('test case 1', function () use (&$registry) {
            test(function () use (&$registry) {
                $registry[] = 'anonymous1';
                $this->assertTrue(true);
            });

            test('test1', function () {
                $this->assertTrue(true);
            });
        });

        testCase(function () use (&$registry) {
            test(function () use (&$registry) {
                $registry[] = 'anonymous2';
                $this->assertTrue(true);
            });
        });

        $result = Extension::run(['filter' => 'Anonymous']);

        $this->assertContains('anonymous1', $registry);
        $this->assertContains('anonymous2', $registry);
    }

    public function testNestingTestCases()
    {
        testCase('main test case', function () {
            testCase('TestCase1', function () {
                test('the test1', function () {
                    $this->assertTrue(true);
                });
            });

            testCase('TestCase2', function () {
                test('the test2', function () {
                    $this->assertTrue(true);
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MainTestCase\\TestCase1::testTheTest1",
            $result
        );
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MainTestCase\\TestCase2::testTheTest2",
            $result
        );
    }

    public function testTheTestCasesWithoutTestsDoNotThrowsWarnings()
    {
        testCase(function () {
            setUp(function () {
                $this->obj = new \stdClass;
            });

            testCase(function () {
                setUp(function () {
                    $this->obj->name = 'dummy';
                });

                test(function () {
                    $this->assertEquals('dummy', $this->obj->name);
                });
            });
        });

        $result = Extension::run();

        $this->assertEmpty($result->warnings());
    }

    public function testNestingTestCases1()
    {
        testCase('test case 1', function () {
            testCase('test case 2', function () {
                testCase('test case 3', function () {
                    test('my test', function () {
                        $this->assertTrue(true);
                    });
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\TestCase1\\TestCase2\\TestCase3::testMyTest",
            $result
        );
    }

    public function testTheTestsOfTheParentTestCaseAreNotExecutedInTheChildTestCase()
    {
        testCase('parent test case', function () {
            test('test1', function () {
                $this->assertTrue(true);
            });

            testCase('child test case', function () {
                test('test2', function () {
                    $this->assertTrue(true);
                });
            });
        });

        $result = Extension::run(['filter' => 'ChildTestCase']);

        $this->assertTestWasNotExecuted(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\ParentTestCase\\ChildTestCase::testTest1",
            $result
        );
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\ParentTestCase\\ChildTestCase::testTest2",
            $result
        );
    }

    public function testSetUpBeforeClassInsideTestCase()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data = uniqid();
            });

            test('test1', function () {
                $this->assertNotEmpty(Registry::$data);
            });

            test('test2', function () {
                $this->assertNotEmpty(Registry::$data);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase::testTest1",
            $result
        );
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase::testTest2",
            $result
        );
    }

    public function testSetUpBeforeClassInsideNestedTestCases()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data = ['MyTestCase' => true];
            });

            testCase('nested 1', function () {
                setUpBeforeClass(function () {
                    Registry::$data['Nested1'] = true;
                });

                testCase('nested 2', function () {
                    setUpBeforeClass(function () {
                        Registry::$data['Nested2'] = true;
                    });

                    test('my test', function () {
                        $this->assertTrue(Registry::$data['MyTestCase']);
                        $this->assertTrue(Registry::$data['Nested1']);
                        $this->assertTrue(Registry::$data['Nested2']);
                    });
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase\\Nested1\\Nested2::testMyTest",
            $result
        );
    }

    public function testSetUpBeforeClassInsideNestedTestCasesWithoutMethodInheritance()
    {
        testCase('my test case', function () {
            setUpBeforeClass(function () {
                Registry::$data['setUpBeforeClass1'] = true;
            });

            testCase('nested 1', function () {
                setUpBeforeClass(function () {
                    Registry::$data['setUpBeforeClass2'] = true;
                }, false);

                test('my test', function () {
                    $this->assertFalse(isset(Registry::$data['setUpBeforeClass1']));
                    $this->assertTrue(Registry::$data['setUpBeforeClass2']);
                });
            });
        });

        $result = Extension::run(['filter' => 'Nested1']);

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase\\Nested1::testMyTest",
            $result
        );
    }

    public function testSetUpInsideTestCase()
    {
        testCase('my test case', function () {
            setUp(function () {
                $this->secret = uniqid();
            });

            test('test1', function () {
                $this->assertNotEmpty($this->secret);
            });

            test('test2', function () {
                $this->assertNotEmpty($this->secret);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase::testTest1",
            $result
        );
        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\MyTestCase::testTest2",
            $result
        );
    }

    public function testSetUpInsideNestedTestCases()
    {
        testCase('parent test case', function () {
            setUp(function () {
                $this->secret1 = uniqid();
            });

            testCase('nested test case 1', function () {
                setUp(function () {
                    $this->secret2 = uniqid();
                });

                testCase('nested test case 2', function () {
                    setUp(function () {
                        $this->secret3 = uniqid();
                    });

                    test('my test', function () {
                        $this->assertNotEmpty($this->secret1);
                        $this->assertNotEmpty($this->secret2);
                        $this->assertNotEmpty($this->secret3);
                    });
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\ParentTestCase\\NestedTestCase1\\NestedTestCase2::testMyTest",
            $result
        );
    }

    public function testSetUpInsideNestedTestCasesWithoutMethoderitance()
    {
        testCase('parent test case', function () {
            setUp(function () {
                $this->secret1 = uniqid();
            });

            testCase('child test case', function () {
                setUp(function () {
                    $this->secret2 = uniqid();
                }, false);

                test('my test', function () {
                    $this->assertFalse(isset($this->secret1));
                    $this->assertNotEmpty($this->secret2);
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            "Andaniel05\\PyramidalTests\\__Dynamic__\\ParentTestCase\\ChildTestCase::testMyTest",
            $result
        );
    }

    public function testTearDownInsideTestCase()
    {
        testCase('my test case', function () {
            test('test1', function () {
                $this->secret = uniqid();
                $this->assertTrue(true);
            });

            test('test2', function () {
                $this->secret = uniqid();
                $this->assertTrue(true);
            });

            tearDown(function () {
                if (! isset(Registry::$data['secret'])) {
                    Registry::$data['secret'] = [];
                }

                Registry::$data['secret'][] = $this->secret;
            });
        });

        $result = Extension::run();

        $this->assertNotEquals(
            Registry::$data['secret'][0],
            Registry::$data['secret'][1]
        );
    }

    public function testTearDownInsideNestedTestCases()
    {
        testCase('my test case', function () {
            tearDown(function () {
                Registry::$data = ['parentTearDown' => true];
            });

            testCase('nested 1', function () {
                test('test1', function () {
                    $this->test1 = true;
                    $this->assertTrue(true);
                });

                tearDown(function () {
                    Registry::$data['test1'] = $this->test1;
                    Registry::$data['childTearDown'] = true;
                });
            });
        });

        $result = Extension::run();

        $this->assertTrue(Registry::$data['test1']);
        $this->assertTrue(Registry::$data['parentTearDown']);
        $this->assertTrue(Registry::$data['childTearDown']);
    }

    public function testTearDownInsideNestedTestCasesWithoutMethodInheritance()
    {
        testCase('my test case', function () {
            tearDown(function () {
                Registry::$data = ['parentTearDown' => true];
            });

            testCase('nested 1', function () {
                test('test1', function () {
                    $this->test1 = true;
                    $this->assertTrue(true);
                });

                tearDown(function () {
                    Registry::$data['test1'] = $this->test1;
                    Registry::$data['childTearDown'] = true;
                }, false);
            });
        });

        $result = Extension::run(['filter' => 'Nested1']);

        $this->assertTrue(Registry::$data['test1']);
        $this->assertFalse(isset(Registry::$data['parentTearDown']));
        $this->assertTrue(Registry::$data['childTearDown']);
    }

    public function testTearDownAfterClassInsideTestCase()
    {
        testCase('my test case', function () {
            test('test1', function () {
                Registry::$data['secret1'] = uniqid();
                $this->assertTrue(true);
            });

            test('test2', function () {
                Registry::$data['secret2'] = uniqid();
                $this->assertTrue(true);
            });

            tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass'] = true;
                static::assertNotEmpty(Registry::$data['secret1']);
                static::assertNotEmpty(Registry::$data['secret2']);
            });
        });

        $result = Extension::run();

        $this->assertTrue(Registry::$data['executedTearDownAfterClass']);
    }

    public function testTearDownAfterClassInsideNestedTestCases()
    {
        testCase('parent test case', function () {
            tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass1'] = true;
            });

            testCase('my test case', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                tearDownAfterClass(function () {
                    static::assertTrue(Registry::$data['executedTearDownAfterClass1']);
                    Registry::$data['executedTearDownAfterClass2'] = true;
                });
            });
        });

        $result = Extension::run();

        $this->assertTrue(Registry::$data['executedTearDownAfterClass1']);
        $this->assertTrue(Registry::$data['executedTearDownAfterClass2']);
    }

    public function testTearDownAfterClassInsideNestedTestCasesWithoutMethodInheritance()
    {
        testCase('parent test case', function () {
            tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass1'] = true;
            });

            testCase('my test case', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                tearDownAfterClass(function () {
                    Registry::$data['executedTearDownAfterClass2'] = true;
                }, false);
            });
        });

        $result = Extension::run(['filter' => 'MyTestCase']);

        $this->assertFalse(isset(Registry::$data['executedTearDownAfterClass1']));
        $this->assertTrue(Registry::$data['executedTearDownAfterClass2']);
    }

    public function expectInvalidContextException()
    {
        $this->expectException(InvalidContextException::class);
        $this->expectExceptionMessage('The context is invalid for this call.');
    }

    public function testSetUpBeforeClassThrowAnExceptionWhenItIsUsedOnInvalidContext()
    {
        $this->expectInvalidContextException();

        setUpBeforeClass(function () {
        });
    }

    public function testSetUpThrowAnExceptionWhenItIsUsedOnInvalidContext()
    {
        $this->expectInvalidContextException();

        setUp(function () {
        });
    }

    public function testTearDownThrowAnExceptionWhenItIsUsedOnInvalidContext()
    {
        $this->expectInvalidContextException();

        tearDown(function () {
        });
    }

    public function testTearDownAfterClassThrowAnExceptionWhenItIsUsedOnInvalidContext()
    {
        $this->expectInvalidContextException();

        tearDownAfterClass(function () {
        });
    }

    public function testSetTestCaseClassDefineTheClassForTheNewTestCases()
    {
        setTestCaseClass(Dummies\TestCase::class);

        testCase('my test case1', function () {
            test('my test', function () {
                $this->assertNotEmpty($this->secret);
            });

            testCase('my test case2', function () {
                test('my test', function () {
                    $this->assertNotEmpty($this->secret);
                });
            });
        });

        testCase('my test case3', function () {
            test('my test', function () {
                $this->assertNotEmpty($this->secret);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase1::testMyTest',
            $result
        );
        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase1\MyTestCase2::testMyTest',
            $result
        );
        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase3::testMyTest',
            $result
        );
    }

    public function testSetTestCaseClassThrowAnInvalidTestCaseClassWhenTheClassIsNotChildOfPHPUnitTestCase()
    {
        $class = 'stdClass';

        $this->expectException(InvalidTestCaseClassException::class);
        $this->expectExceptionMessage("The class '$class' is not child of '\PHPUnit\Framework\TestCase'.");

        setTestCaseClass($class);
    }

    public function testSetTestCaseClassDoNotThrowTheExceptionIfTheClassIsTheDefaultPHPUnitTestCase()
    {
        setTestCaseClass('PHPUnit\Framework\TestCase');

        $this->assertTrue(true);
    }

    public function testSetTestCaseNamespace()
    {
        setTestCaseNamespace('Andaniel05\NewNamespace');

        testCase('my test case1', function () {
            test('my test', function () {
                $this->assertTrue(true);
            });

            testCase('my test case2', function () {
                test('my test', function () {
                    $this->assertTrue(true);
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\NewNamespace\MyTestCase1::testMyTest',
            $result
        );
        $this->assertTestWasSuccessful(
            'Andaniel05\NewNamespace\MyTestCase1\MyTestCase2::testMyTest',
            $result
        );
    }

    public function testSetTestCaseNamespaceChangesTheNamespaceForNewTestCases()
    {
        setTestCaseNamespace('MyNamespace1');

        testCase('test case 1', function () {
            test('test1', function () {
                $this->assertTrue(true);
            });
        });

        setTestCaseNamespace('MyNamespace2');

        testCase('test case 2', function () {
            test('test1', function () {
                $this->assertTrue(true);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('MyNamespace1\TestCase1::testTest1', $result);
        $this->assertTestWasSuccessful('MyNamespace2\TestCase2::testTest1', $result);
    }

    public function testThrowADuplicatedTestExceptionWhenExistsDuplicatedTestNames()
    {
        $this->expectException(DuplicatedTestException::class);
        $this->expectExceptionMessage("In the test case 'Andaniel05\PyramidalTests\__Dynamic__\MainTestCase' already exists a test with name equal to 'testTest1'.");

        testCase('main test case', function () {
            test('test1', function () {
            });

            test('test1', function () {
            });
        });
    }

    public function testDeepNestingOfTestCases()
    {
        testCase('test case 1', function () {
            testCase('test case 2', function () {
                testCase('test case 3', function () {
                    testCase('test case 4', function () {
                        testCase('test case 5', function () {
                            test('test1', function () {
                                $this->assertTrue(true);
                            });
                        });
                    });
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\TestCase1\TestCase2\TestCase3\TestCase4\TestCase5::testTest1', $result);
    }

    public function testSupportOfInvalidNames()
    {
        $invalidName = '!@#$%^&*()/*-+\\|?`';

        testCase($invalidName, function () use ($invalidName) {
            test($invalidName, function () {
                $this->assertTrue(true);
            });
        });

        $result = Extension::run();

        $this->assertCount(1, $result->passed());
    }

    public function testTopClassesOfTestCases()
    {
        setTestCaseClass(DummyTestCase::class);

        testCase('TestCase1', function () {
            test('test1', function () {
                $this->assertInstanceOf(DummyTestCase::class, $this);
            });
        });

        setTestCaseClass(PHPUnitTestCase::class);

        testCase('TestCase2', function () {
            test('test1', function () {
                $this->assertInstanceOf(PHPUnitTestCase::class, $this);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\TestCase1::testTest1',
            $result
        );
        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\TestCase2::testTest1',
            $result
        );
    }

    public function testCreateMethodThrowAnExceptionWhenItIsUsedOnInvalidContext()
    {
        $this->expectInvalidContextException();

        createMethod('newMethod', function () {
        });
    }

    public function testCreateMethodThrowAnInvalidMethodNameException()
    {
        $method = '123method';
        $this->expectException(InvalidMethodNameException::class);
        $this->expectExceptionMessage("The name '{$method}' is invalid for a method.");

        testCase(function () use ($method) {
            createMethod($method, function () {
            });
        });
    }

    public function testCreateMethodInteractingWithTheContext()
    {
        testCase('my test case', function () {
            createMethod('installData', function () {
                $this->data = uniqid(); // Create the data attribute
            });

            test('test1', function () {
                $this->installData(); // Invoke to the new method

                $this->assertNotEmpty($this->data);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testCreateMethodWhenItReturnsAValue()
    {
        testCase('my test case', function () {
            createMethod('getSecret', function () {
                return 'secret';
            });

            test('test1', function () {
                $this->assertEquals('secret', $this->getSecret());
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testCreateMethodWithArguments()
    {
        testCase('my test case', function () {
            createMethod('sum', function (int $a, int $b): int {
                return $a + $b;
            });

            test('test1', function () {
                $this->assertEquals(9, $this->sum(5, 4));
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testCreateMethodInChildTestCases()
    {
        testCase('my parent test case', function () {
            createMethod('stringVal', function ($val) {
                return strval($val);
            });

            testCase('my child test case', function () {
                createMethod('sum', function (int $a, int $b) {
                    return $this->stringVal($a + $b);
                });

                test('test1', function () {
                    $this->assertSame('9', $this->sum(5, 4));
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyParentTestCase\MyChildTestCase::testTest1',
            $result
        );
    }

    public function testHeritanceOfMethods()
    {
        testCase('my parent test case', function () {
            createMethod('sum', function (int $a, int $b) {
                return $a + $b;
            });

            testCase('my child test case', function () {
                createMethod('sum', function (int $a, int $b) {
                    return strval(parent::sum($a, $b));
                });

                test('test1', function () {
                    $this->assertSame('9', $this->sum(5, 4));
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyParentTestCase\MyChildTestCase::testTest1',
            $result
        );
    }

    public function testCreateStaticMethodThrowAnExceptionWhenItIsUsedOnInvalidContext()
    {
        $this->expectInvalidContextException();

        createStaticMethod('newMethod', function () {
        });
    }

    public function testCreateStaticMethodThrowAnInvalidMethodNameException()
    {
        $method = '123method';
        $this->expectException(InvalidMethodNameException::class);
        $this->expectExceptionMessage("The name '{$method}' is invalid for a method.");

        testCase(function () use ($method) {
            createStaticMethod($method, function () {
            });
        });
    }

    public function testUsingStaticMethodsInsideTests()
    {
        $registry = [];

        testCase('my test case', function () use (&$registry) {
            createStaticMethod('putDataInRegistry', function () use (&$registry) {
                $registry['data'] = uniqid();
            });

            test('test1', function () use (&$registry) {
                static::putDataInRegistry();

                $this->assertNotEmpty($registry['data']);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testUsingStaticMethodsInsideStaticFunctionsOfTheClass()
    {
        $registry = [];

        testCase('my test case', function () use (&$registry) {
            createStaticMethod('putDataInRegistry', function () use (&$registry) {
                $registry['data'] = uniqid();
            });

            setUpBeforeClass(function () {
                static::putDataInRegistry();
            });

            test('test1', function () use (&$registry) {
                $this->assertNotEmpty($registry['data']);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testCreateStaticMethodWhenItReturnsAValue()
    {
        testCase('my test case', function () {
            createStaticMethod('getSecret', function () {
                return 'secret';
            });

            test('test1', function () {
                $this->assertEquals('secret', static::getSecret());
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testCreateStaticMethodWithArguments()
    {
        testCase('my test case', function () {
            createStaticMethod('sum', function (int $a, int $b): int {
                return $a + $b;
            });

            test('test1', function () {
                $this->assertEquals(9, static::sum(5, 4));
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1',
            $result
        );
    }

    public function testCreateStaticMethodInChildTestCases()
    {
        testCase('my parent test case', function () {
            createStaticMethod('stringVal', function ($val) {
                return strval($val);
            });

            testCase('my child test case', function () {
                createStaticMethod('sum', function (int $a, int $b) {
                    return static::stringVal($a + $b);
                });

                test('test1', function () {
                    $this->assertSame('9', static::sum(5, 4));
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyParentTestCase\MyChildTestCase::testTest1',
            $result
        );
    }

    public function testHeritanceOfStaticMethods()
    {
        testCase('my parent test case', function () {
            createStaticMethod('sum', function (int $a, int $b) {
                return $a + $b;
            });

            testCase('my child test case', function () {
                createStaticMethod('sum', function (int $a, int $b) {
                    return strval(parent::sum($a, $b));
                });

                test('test1', function () {
                    $this->assertSame('9', static::sum(5, 4));
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful(
            'Andaniel05\PyramidalTests\__Dynamic__\MyParentTestCase\MyChildTestCase::testTest1',
            $result
        );
    }

    public function testUseMacroThrowAnExceptionWhenItIsUsedOverATestCase()
    {
        $this->expectInvalidContextException();

        useMacro('my macro');
    }

    public function testUseMacroThrowAnMacroNotFoundException()
    {
        $this->expectException(MacroNotFoundException::class);
        $this->expectExceptionMessage("The macro 'my macro' not exists.");

        testCase(function () {
            useMacro('my macro');
        });
    }

    public function testUseMacroThrowAnMacroNotFoundException2()
    {
        $this->expectException(MacroNotFoundException::class);
        $this->expectExceptionMessage("The macro 'my macro' not exists.");

        testCase('parent test case', function () {
            testCase('child test case 1', function () {
                createMacro('my macro', function () {
                    test('test1', function () {
                        $this->assertTrue(true);
                    });

                    test('test2', function () {
                        $this->assertTrue(true);
                    });
                });
            });

            testCase('child test case 1', function () {
                useMacro('my macro');
            });
        });
    }

    public function testUsageOfGlobalMacros()
    {
        createMacro('my macro', function () {
            test('test1', function () {
                $this->assertTrue(true);
            });

            test('test2', function () {
                $this->assertTrue(true);
            });
        });

        testCase('my test case', function () {
            useMacro('my macro');
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest2', $result);
    }

    public function testUsageOfMacrosWithTests1()
    {
        testCase('my test case', function () {
            createMacro('my macro', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                test('test2', function () {
                    $this->assertTrue(true);
                });
            });

            useMacro('my macro');
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest2', $result);
    }

    public function testUsageOfMacrosWithTests2()
    {
        testCase('parent test case', function () {
            createMacro('my macro', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                test('test2', function () {
                    $this->assertTrue(true);
                });
            });

            testCase('child test case', function () {
                useMacro('my macro');
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase::testTest2', $result);
    }

    public function testUsageOfMacrosWithTests3()
    {
        testCase('parent test case', function () {
            createMacro('my macro', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                test('test2', function () {
                    $this->assertTrue(true);
                });
            });

            testCase('child test case 1', function () {
                testCase('child test case 2', function () {
                    testCase('child test case 3', function () {
                        testCase('child test case 4', function () {
                            useMacro('my macro');
                        });
                    });
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase1\ChildTestCase2\ChildTestCase3\ChildTestCase4::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase1\ChildTestCase2\ChildTestCase3\ChildTestCase4::testTest2', $result);
    }

    public function testUsageOfMacrosWithTests4()
    {
        testCase('parent test case', function () {
            createMacro('my macro', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                test('test2', function () {
                    $this->assertTrue(true);
                });
            });

            testCase('child test case 1', function () {
                createMacro('my macro', function () {
                    test('test3', function () {
                        $this->assertTrue(true);
                    });

                    test('test4', function () {
                        $this->assertTrue(true);
                    });
                });

                testCase('child test case 2', function () {
                    useMacro('my macro');
                });
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase1\ChildTestCase2::testTest3', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase1\ChildTestCase2::testTest4', $result);
    }

    public function testUsageOfMacrosWithTests5()
    {
        createMacro('my macro', function () {
            test('test1', function () {
                $this->assertTrue(true);
            });

            test('test2', function () {
                $this->assertTrue(true);
            });
        });

        testCase('parent test case', function () {
            createMacro('my macro', function () {
                test('test3', function () {
                    $this->assertTrue(true);
                });

                test('test4', function () {
                    $this->assertTrue(true);
                });
            });

            testCase('child test case', function () {
                useMacro('my macro');
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase::testTest3', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase::testTest4', $result);
    }

    public function testUsageOfMacrosWithTestCases1()
    {
        createMacro('my macro', function () {
            testCase('child test case', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });

                test('test2', function () {
                    $this->assertTrue(true);
                });
            });
        });

        testCase('parent test case', function () {
            useMacro('my macro');
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase::testTest2', $result);
    }

    public function testUsageOfMacrosWithTestCases2()
    {
        testCase('parent test case', function () {
            createMacro('my macro', function () {
                testCase('child test case 2', function () {
                    test('test1', function () {
                        $this->assertTrue(true);
                    });

                    test('test2', function () {
                        $this->assertTrue(true);
                    });
                });
            });

            testCase('child test case 1', function () {
                useMacro('my macro');
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase1\ChildTestCase2::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\ParentTestCase\ChildTestCase1\ChildTestCase2::testTest2', $result);
    }

    public function testUsageOfMacrosWithTestCases3()
    {
        createMacro('my macro', function () {
            testCase('shared test case', function () {
                test('test1', function () {
                    $this->assertTrue(true);
                });
            });
        });

        testCase('test case 1', function () {
            useMacro('my macro');
        });

        testCase('test case 2', function () {
            useMacro('my macro');
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\TestCase1\SharedTestCase::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\TestCase2\SharedTestCase::testTest1', $result);
    }

    public function testSetUpBeforeClassInsideAMacro()
    {
        createMacro('my macro', function () {
            setUpBeforeClass(function () {
                Registry::$data = uniqid();
            });
        });

        testCase('my test case', function () {
            useMacro('my macro');

            test('test1', function () {
                $this->assertNotEmpty(Registry::$data);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1', $result);
    }

    public function testSetUpInsideAMacro()
    {
        createMacro('my macro', function () {
            setUp(function () {
                $this->secret = uniqid();
            });
        });

        testCase('my test case', function () {
            useMacro('my macro');

            test('test1', function () {
                $this->assertNotEmpty($this->secret);
            });

            test('test2', function () {
                $this->assertNotEmpty($this->secret);
            });
        });

        $result = Extension::run();

        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest1', $result);
        $this->assertTestWasSuccessful('Andaniel05\PyramidalTests\__Dynamic__\MyTestCase::testTest2', $result);
    }

    public function testTearDownInsideAMacro()
    {
        createMacro('my macro', function () {
            tearDown(function () {
                if (! isset(Registry::$data['secret'])) {
                    Registry::$data['secret'] = [];
                }

                Registry::$data['secret'][] = $this->secret;
            });
        });

        testCase('my test case', function () {
            useMacro('my macro');

            test('test1', function () {
                $this->secret = uniqid();
                $this->assertTrue(true);
            });

            test('test2', function () {
                $this->secret = uniqid();
                $this->assertTrue(true);
            });
        });

        $result = Extension::run();

        $this->assertNotEquals(
            Registry::$data['secret'][0],
            Registry::$data['secret'][1]
        );
    }

    public function testTearDownAfterClassInsideAMacro()
    {
        createMacro('my macro', function () {
            tearDownAfterClass(function () {
                Registry::$data['executedTearDownAfterClass'] = true;
                static::assertNotEmpty(Registry::$data['secret1']);
                static::assertNotEmpty(Registry::$data['secret2']);
            });
        });

        testCase('my test case', function () {
            useMacro('my macro');

            test('test1', function () {
                Registry::$data['secret1'] = uniqid();
                $this->assertTrue(true);
            });

            test('test2', function () {
                Registry::$data['secret2'] = uniqid();
                $this->assertTrue(true);
            });
        });

        $result = Extension::run();

        $this->assertTrue(Registry::$data['executedTearDownAfterClass']);
    }
}
