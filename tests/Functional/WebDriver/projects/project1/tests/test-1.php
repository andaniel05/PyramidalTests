<?php

use PHPUnit\Framework\TestCase;
use ThenLabs\PyramidalTests\Annotation\ImportDecorators;

/**
 * @ImportDecorators({
 *     "ThenLabs\PyramidalTests\Utils\WebDriverDecorators",
 *     "ThenLabs\PyramidalTests\Utils\SleepDecorators",
 * })
 */
class MyTestCase extends TestCase
{
}

setTestCaseClass('MyTestCase');

$text = uniqid('text');
$text2 = uniqid('text');

testCase('show an alert')
    ->navigate('file://'.__DIR__.'/index.html')
    ->type($text, '#input')
    ->click('#button')
    ->waitForAlert($text)
    ->sleep(1)
    ->usleep(1)
    ->acceptAlert()
    ->test(function () {
        $this->assertTrue(true);
    })

    ->testCase('show other alert')
        ->clear('#input')
        ->type($text2, '#input')
        ->click('#button')
        ->waitForAlert()
        ->test('', function () use ($text2) {
            $alert = static::$driver->switchTo()->alert();
            $this->assertEquals($text2, $alert->getText());
        })
    ->endTestCase()
->endTestCase();
