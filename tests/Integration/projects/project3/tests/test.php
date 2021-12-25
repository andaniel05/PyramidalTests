<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Annotation\ImportDecorators;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\ExpectSystemChangeTrait;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader\SqLiteReader;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SnapshotsInDecoratorsInterface;

$pdo = new PDO('sqlite:'.__DIR__.'/../../../app/db.sqlite');
SystemSnapshot::addReader('db', new SqLiteReader($pdo));

/**
 * @ImportDecorators({
 *     "ThenLabs\PyramidalTests\Utils\WebDriverDecorators",
 *     "ThenLabs\PyramidalTests\Extension\SystemSnapshot\Decorators",
 * })
 */
class MyTestCase extends TestCase implements SnapshotsInDecoratorsInterface
{
    use ExpectSystemChangeTrait;
}

setTestCaseClass(MyTestCase::class);

testCase()
    ->navigate('http://localhost:8080')
    ->type('Antonio', '#name')
    ->type('60', '#age')
    ->click('#submit')
    ->expectSystemChange([
        'db' => [
            'sqlite_sequence' => [
                [
                    'seq' => '3'
                ]
            ],
        ]
    ])
    ->test(function () {
        $this->expectSystemChange([
            'db' => [
                'persons' => [
                    2 => [
                        'id' => '3',
                        'name' => 'Antonio',
                        'age' => '60'
                    ]
                ]
            ]
        ]);
    })
;
