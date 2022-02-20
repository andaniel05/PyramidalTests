
# Creating the tests.

As mentioned before, [PyramidalTests][PyramidalTests] is based on dynamically building the classes that will ultimately be executed by [PHPUnit][PHPUnit].

>The dynamic construction of the classes is achieved thanks to the [ClassBuilder][ClassBuilder] project.

The classes and their members are defined with calls to functions whose names correspond to the key methods of [PHPUnit][PHPUnit]. For example:

```php
<?php

testCase('title of the test case', function () {
    setUpBeforeClass(function () {
        // ...
    });

    setUp(function () {
        // ...
    });

    test('title of the test', function () {
        // ...
    });

    tearDown(function () {
        // ...
    });

    tearDownAfterClass(function () {
        // ...
    });
});
```

From the code above you can easily deduce what defines each of the functions used. It would be generating a class equivalent to the following:

```php
<?php

/**
 * @testdox title of the test case
 */
class TestCaseXYZ extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        // ...
    }

    public function setUp(): void
    {
        // ...
    }

    /**
     * @testdox title of the test
     */
    public function test1(): void
    {
        // ...
    }

    public function tearDown(): void
    {
        // ...
    }

    public static function tearDownAfterClass(): void
    {
        // ...
    }
}
```

>It is very important to clarify that at no time does the user interact with the generated class, in fact, this is a totally transparent process. We only put this example so that the use of the functions is better understood.

## Defining members to classes.

When working with [PHPUnit][PHPUnit] it is often the case that new methods and properties are added to classes. This can be achieved as follows:

```php
<?php

testCase('title of the test case', function () {
    useTrait('My\OwnTrait', [
        'myTraitMethod as protected',
    ]);

    property('myProperty');

    staticProperty('myStaticProperty', 100); // with default value.

    method('sum', function (int $a, int $b): int {
        return $a + $b;
    });

    staticMethod('rest', function (int $a, int $b): int {
        return $a - $b;
    });

    // ...
});
```

Functions used to create methods and properties return objects of type [Method](https://github.com/thenlabs/class-builder/blob/1.0/src/Model/Method.php) or [Property](https: //github.com/thenlabs/class-builder/blob/1.0/src/Model/Property.php) as the case may be. These results are [*builders*](https://en.wikipedia.org/wiki/Builder_pattern) that will allow you to customize any aspect of the member. For example, if you want to add an annotation in the comment block, you could do it as follows:

```php
<?php

testCase('title of the test case', function () {
    method('sum', function () {
        // ...
    })->addComment('@group group1');

    // ...
});
```

### Working with data providers.

In this same way we could define and use data providers.

Thanks to the `with(array $data)` method it is possible to create [tests with data providers](https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html#data-providers) .

```php
<?php

test('a test with data provider', function ($a, $b, $result) {
    $this->assertSame($result, $a + $b);
})->with([
    [1, 1, 2],
    [2, 2, 4],
]);
```

## Defining the base class of the test cases.

The default base class is `PHPUnit\Framework\TestCase` but when you need to use another one you can do it like this:

```php
<?php

setTestCaseClass(MyCustomTestCase::class);

testCase('title of the test case', function () {
    // ...
});
```

## Nesting and reuse of test cases.

As previously mentioned, one of the greatest advantages offered by [PyramidalTests][PyramidalTests] is that it allows nesting of test cases, which enables code reuse.

When two or more cases are nested, the classes of the innermost ones will inherit from the outermost ones but keep in mind that **tests are not inherited**.

The following example shows that a property defined in the parent case's `setUp()` method is being used in the test.

```php
<?php

testCase('the parent test case', function () {
    setUp(function () {
        $this->property1 = true;
    });

    // ...

    testCase('the child test case', function () {
        setUp(function () {
            $this->property2 = true;
        });

        test('my test', function () {
            $this->assertTrue($this->property1);
            $this->assertTrue($this->property2);
        });
    });
});
```

This happens because each key method of [PHPUnit][PHPUnit](`setUp` in this case) calls the parent before its execution, but this can be disabled by passing `false` as the second argument to the call to that function. Example:

```php
<?php

testCase('the parent test case', function () {
    setUp(function () {
        $this->property1 = true;
    });

    // ...

    testCase('the child test case', function () {
        setUp(function () {
            $this->property2 = true;
        }, false); // indicating false.

        test('my test', function () {
            $this->assertObjectNotHasAttribute('property1', $this);
            $this->assertTrue($this->property2);
        });
    });
});
```

## Working with macros.

With the aim of facilitating the reuse of tests and cases as much as possible, the so-called macros exist.

In the following example, a macro has been created which contains a test that depends on the `myProperty` property of the context. You can see that such a test is reused in two cases that have the `myProperty` property but are created in different ways.

```php
<?php

macro('my macro', function () {
    test('my test', function () {
        $this->assertTrue($this->myProperty);
    });
});

testCase('my test case 1', function () {
    property('myProperty', true);

    useMacro('my macro');
});

testCase('my test case 2', function () {
    setUp(function () {
        $this->myProperty = true;
    });

    useMacro('my macro');
});
```

It is also important to mention that macros can be created within test cases and can also be extended as follows:

```php
<?php

testCase('my test case', function () {
    macro('my macro', function (\Closure $extend) {
        test('test 1', function () {
            $this->assertTrue(true);
        });

        $extend();
    });

    useMacro('my macro', function () {
        test('test 2', function () {
            $this->assertTrue(true);
        });
    });
});
```

## Working with tests and cases without titles.

Up to this point we have always seen that calls to the `testCase()` and `test()` functions always specify a title as their first argument. Something that can be useful in certain cases is to skip those titles and instead focus directly on the implementation of the code. Example:

```php
<?php
// tests/test-example.php

testCase(function () {
    test(function () {
        $this->assertTrue(true);
    });
});
```

If, when executing these tests, we did it by specifying the `--testdox` option, a result like the following would be displayed:

<pre class="text-white p-2"><font color="#C4A000"><b>PyramidalTests 2.x.x</b></font> by Andy Daniel Navarro Taño and contributors.
PHPUnit 9.5.x by Sebastian Bergmann and contributors.

<u style="text-decoration-style:single">tests/test-example.php</u>
 <font color="#4E9A06">✔</font> test1 :5

Time: 00:00.004, Memory: 6.00 MB

<span style="background-color:#4E9A06"><font color="#2E3436">OK (1 test, 1 assertion)</font></span></pre>

As can be seen, in these situations the relative path of the file where it is defined will be displayed as the title of the case, and as title for the tests the prefix `test` will be used followed by the corresponding number, and finally, the number of the line where the `test()` function was called.

### Creating tests without defining a parent case.

A result similar to the above could be obtained by skipping the `testCase()` call, as the following example shows. This can be useful in files that only contain a single case.

```php
<?php
// tests/test-example.php

setUpBeforeClass(function () {
    // ...
});

setUp(function () {
    // ...
});

test(function () {
    $this->assertTrue(true);
});

// ...
```

## Showing other ways to create tests.

The style we've shown so far uses functions whose names correspond to key functions in [PHPUnit][PHPUnit]. But additionally, other functions are available whose names constitute another style that is quite popular in other projects and preferred by many developers.

The following snippet is fully equivalent to the one shown in the first example of this document. The comments show the function equivalent to the original style.

### Describe-It.

```php
<?php

describe('my custom title', function () { // testCase()
    beforeAll(function () { // setUpBeforeClass()
        // ...
    });

    beforeEach(function () { // setUp()
        // ...
    });

    it('title of the test', function () { // test()
        // ...
    });

    afterEach(function () { // tearDown()
        // ...
    });

    afterAll(function () { // tearDownAfterClass()
        // ...
    });
});
```

We have seen that this style has become popular in the [PHP][PHP] community thanks to the excellent *framework* [Pest][Pest]. One of the features that users prefer the most is to use expectations instead of [PHPUnit][PHPUnit]'s asserts.

#### Using expectations instead of asserts.

If we wanted to make use of this feature, we would first have to install the [pest-plugin-expectations](https://github.com/pestphp/pest-plugin-expectations) package:

```text
$ composer require pestphp/pest-plugin-expectations --dev
```

And in this way we could make use of them:

```php
<?php

test(function () {
    expect(true)->toBe(true); // Pest expectations.
});
```

### Using decorators.

Decorators offer users simplified ways of doing things. We'll cover this in depth later, but for now we want to show that there is an additional style for creating tests and cases:

```php
<?php

testCase('title of the test case')
    ->setUpBeforeClass(function () {
        // ...
    })

    ->setUp(function () {
        // ...
    })

    ->test('title of the test', function () {
        // ...
    })

    ->testCase()
        ->test(function () {
            // ...
        })
    ->end()

    ->tearDown(function () {
        // ...
    })

    ->tearDownAfterClass(function () {
        // ...
    })
;
```

---

<span class="float-start">Back: [The pyramidal command](the-pyramidal-command.md)</span>
<span class="float-end">Next: [Creating end to end tests](end-to-end.md)</span>

[Pest]: https://pestphp.com/
[PHP]: https://www.php.net/
[PHPUnit]: https://phpunit.de/
[ClassBuilder]: https://github.com/thenlabs/class-builder
[PyramidalTests]: https://pyramidal-tests.thenlabs.org