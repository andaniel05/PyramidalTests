<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit;

use ReflectionClass;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestWithoutParentTestCaseTest extends UnitTestCase
{
    public function testCreatingATestWithoutAParentTestCase()
    {
        test($testTitle = uniqid('my title'), $closure = function () {
            $this->assertTrue(true);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($closure), $result);

        $testModel = $this->getTestModelFromClosure($closure);
        $testCaseModel = $testModel->getParent();

        $class = new ReflectionClass($testCaseModel->getClassBuilder()->getFCQN());
        $method = $class->getMethod($testModel->getMethodName());

        $this->assertStringContainsString("@testdox {$testTitle}", $method->getDocComment());
    }

    public function testCreatingAnUntitledTestWithoutAParentTestCase()
    {
        test($closure = function () {
            $this->assertTrue(true);
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($closure), $result);
    }

    public function testStubsSupport()
    {
        test('stubs support', $closure = function () {
            $stub = $this->createMock(SomeClass::class);

            $stub->method('doSomething')
                 ->willReturn('foo')
            ;

            $this->assertSame('foo', $stub->doSomething());
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($closure), $result);
    }

    public function testMocksSupport()
    {
        test('mocks support', $closure = function () {
            $observer = $this->getMockBuilder(Observer::class)
                             ->setMethods(['update'])
                             ->getMock()
            ;

            $observer->expects($this->once())
                     ->method('update')
                     ->with($this->equalTo('something'))
            ;

            $subject = new Subject('My subject');
            $subject->attach($observer);

            $subject->doSomething();
        });

        $result = $this->runTests();

        $this->assertExpectedTotals(['success' => 1], $result);
        $this->assertTestWasExecuted($this->getTestNameFromClosure($closure), $result);
    }
}
