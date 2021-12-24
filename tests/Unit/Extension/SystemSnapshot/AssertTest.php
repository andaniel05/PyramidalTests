<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit\Extension\SystemSnapshot;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Assert;
use ThenLabs\PyramidalTests\Tests\Unit\UnitTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AssertTest extends UnitTestCase
{
    public function testAssertExpectedArrayDiffThrownAnExpectationFailedExceptionWhenDiffDoNotMatchWithExpectations()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("The difference has not the data 'db1'.");

        $array1 = [];
        $array2 = ['db' => [1, 2, 3]];

        $expects = ['db1' => []];

        Assert::assertExpectedArrayDiff($array1, $array2, $expects);
    }

    public function testAssertExpectedArrayDiffThrownAnAssertionFailedError()
    {
        $array1 = [];
        $array2 = ['db' => [1, 2, 3]];

        $exceptionMessage = <<<MSG
        Unexpected Array Diff:
        [
            'db' => [
                1,
                2,
                3
            ]
        ]
        MSG;

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($exceptionMessage);

        Assert::assertExpectedArrayDiff($array1, $array2);
    }

    public function testAssertExpectedArrayDiff1()
    {
        $array1 = ['db' => []];
        $array2 = $array1;

        Assert::assertExpectedArrayDiff($array1, $array2);
    }

    public function testAssertExpectedArrayDiff2()
    {
        $array1 = [];
        $array2 = ['db' => []];

        $expects = ['db' => []];

        Assert::assertExpectedArrayDiff($array1, $array2, $expects);
    }

    public function testAssertExpectedArrayDiff3()
    {
        $array1 = [];
        $array2 = ['db' => []];

        $expects = ['db' => $this->equalTo([])];

        Assert::assertExpectedArrayDiff($array1, $array2, $expects);
    }

    public function testAssertExpectedArrayDiff4()
    {
        $array1 = [];
        $array2 = ['db' => []];

        $expects = ['db' => $this->equalTo([])];

        Assert::assertExpectedArrayDiff($array2, $array1, $expects);
    }

    public function testAssertExpectedArrayDiff5()
    {
        $array1 = [];
        $array2 = ['db' => []];

        $expects = ['db' => function () {
            return true;
        }];

        Assert::assertExpectedArrayDiff($array1, $array2, $expects);
    }

    public function testAssertExpectedArrayDiff6()
    {
        $array1 = [
            'db' => [
                'table1' => [
                    ['id' => 0, 'col1' => 'val1', 'col2' => uniqid()],
                ],
            ],
        ];

        $array2 = $array1;
        $array2['db']['table1'][0]['col1'] = 'val2';

        $expects = [
            'db' => [
                'table1' => [
                    ['col1' => $this->equalTo('val2')],
                ],
            ],
        ];

        Assert::assertExpectedArrayDiff($array1, $array2, $expects);
    }
}
