# Working with decorators.

So far we have seen that test and case implementations are done by using [closures](https://www.php.net/manual/functions.anonymous.php) functions, but alternatively, it is also It is possible to make certain implementations by means of successive calls to the models.

>Models are the values returned by the functions used to create the tests and cases. In the case of `testCase()` and `describe()` they return an object of type `ThenLabs\PyramidalTests\Model\TestCaseModel` while `test()` and `it()` return an object of type ` ThenLabs\PyramidalTests\Model\TestModel`.

## Creating tests with decorators.

The following example shows a test that has been created without specifying a [closure][closure] function (and not even a title). Instead, `assertTrue(true)` has been called directly on the model and its implementation has been carried out in this way.

```php
<?php

test()->assertTrue(true);
```

>This way of creating tests is inspired by [Pest's higher order tests](https://pestphp.com/docs/higher-order-tests).

These implementations can contain multiple decorators and these correspond to the **non-static** methods of the class in question.

```php
<?php

class MyTestCase extends \PHPUnit\Framework\TestCase
{
    public function doAnything()
    {
        // ...
    }
}

setTestCaseClass(MyTestCase::class);

test('my title')
    ->doAnything()
    ->assertSame(50, 50);
```

In situations where certain methods return objects, it is possible to use the methods of those objects as decorators as well. The following example is for a test of a [Laravel][Laravel] project where the `get()` method returns an object containing the `assertStatus()` method.

```php
<?php

setTestCaseClass('Tests\TestCase');

test('test title')
    ->get('/')
    ->assertStatus(200)
;
```

If in these cases you need to return to the previous context, we recommend that you use margins and call the `end()` decorator as in the following example:

```php
<?php

setTestCaseClass('Tests\TestCase');

test('test title')
    ->get('/')
        ->assertStatus(200)
    ->end()
    ->assertTrue(true)
;
```

## Creating test cases with decorators.

In a very similar way to tests, it is also possible to make use of this feature in cases, although with some differences.

The first thing to mention is that the decorators will be executed in the `setUpBeforeClass()` method of the case, and therefore, they can only be used with the **static methods** of the class in question. Example:

```php
<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;

class MyTestCase extends \PHPUnit\Framework\TestCase
{
    public static function launchBrowser(): RemoteWebDriver
    {
        return RemoteWebDriver::create(/*...*/);
    }
}

setTestCaseClass(MyTestCase::class);

testCase('my title')
    ->launchBrowser()
        ->get('https://thenlabs.org')
    ->end()
    ->test('title of test1', function () {
        // ...
    })
    ->test('title of test2', function () {
        // ...
    })
;
```


---

<span class="float-start">Back: [Creating end to end tests](end-to-end.md)</span>
<span class="float-end">Next: [Configuration options](config-options.md)</span>

[PHPUnit]: https://phpunit.de/
[Pest]: https://pestphp.com/
[Laravel]: https://laravel.com/
[PyramidalTests]: https://pyramidal-tests.thenlabs.org
[closure]: https://www.php.net/manual/es/functions.anonymous.php