# PyramidalTests

## Introduction.

PyramidalTests is a testing framework based on PHPUnit wich permit create the tests employing nested anonymous functions. This method have some adventages over the traditional method of PHPUnit wich require create a class per each case.

The main adventage is that the tests are created much more fastly since it's writed less code, and also, it's easier the reutilization of it since by default, the nested test cases inherit from his parents.

Other of the adventages that offers this method consists in that the tests will be described with a natural language, wich will easier that with read the execution result, it's will understand better the functionment of the system under tests(SUT), although it will depend of the explicit that achieve or wish be the developer by telling in a history wich it's be testing.

Example 1:

```php
<?php

use ThenLabs\PyramidalTests\Demo\Product;
use ThenLabs\PyramidalTests\Demo\Category;

testCase('it is created a product', function () {
    setUp(function () {
        $this->product = new Product;
    });

    test('the product has a #CODE#', function () {
        $code = $this->product->getCode();

        $this->assertRegExp('/#\d+#/', $code);
        $this->assertSame($code, $this->product->getCode());
    });

    test('the product not contains categories', function () {
        $this->assertCount(0, $this->product->getCategories());
    });

    testCase('adds a category to the product', function () {
        setUp(function () {
            $this->category = $this->createMock(Category::class);

            $this->product->addCategory($this->category);
        });

        test('the product contains the category', function () {
            $this->assertContains($this->category, $this->product->getCategories());
        });
    });
});
```

With the last example we pretends to highlight several aspects.

The first of them consists in the way to create the tests. You can see that with functions like `testCase()`, `test()`, `setUp()`, etc, it implements in a very easy way all the logic of the target test case wich it's described employing a natural language.

In addition to this, it can be seen that PHPUnit's own utilities are available, such as the methods to create asserts, *mocks*, etc., and it is that by default, the test cases inherit from the `PHPUnit\Framework\TestCase` class.

To ending, we want to highlight that the more nested cases, inherit from the more externs, wich easier the utilization of the code considerately.

## Installation.

    $ composer require --dev thenlabs/pyramidal-tests 2.0.x-dev

>Note that currently we have not a stable version yet.

## Working with PyramidalTests.

PyramidalTests is built on the basis of PHPUnit so his philosophy is the same practically.

After PyramidalTests is installed in a project, it may will be executed with the command `./vendor/bin/pyramidal`.

Once PyramidalTests is installed in a project, it can be executed with the command `./vendor/bin/pyramidal`.

May be checked that this command presents the same options that the orifinal PHPUnit so all the your knowledge about PHPUnit may be applieds also to the work with PyramidalTests. Only will exists some details wich we comments nextly.

### Creating the tests.

The tests created with PyramidalTests should be stored in files wich names should to matchs with the pattern `test-*.php`.

The content of that files should be equal to shown in the example 1 of this guide. Like we shown, the test cases will be created employing the next functions:

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

#### Creating tests and cases without titles.

We want to comment that give a title to a test case or a test, it is a thing optional and they can be created also in the next way:

```php
<?php

testCase(function () {
    test(function () {
        // ...
    });
});
```

This can be useful in situations where we thinks in a source code and we obviate natural language descriptions.

>This also may be obtained if we specify an empty string in the title, but this way is much more simplify.

#### Creating tests with a parent test case.

In the test files also it is possible to call to the `test()` function from the root, and this way it will created automatically a parent test case.

```php
<?php

test(function () {
    // ...
});
```

#### Specifying the class for the test cases.

Like we say earlier, by default all the test cases inherit from the `PHPUnit\Framework\TestCase` class.

When needs to specify another class for them, it is possible doing in the next way:

```php
<?php

setTestCaseClass('My\Own\TestCase');

testCase('', function () {
    // ...
});
```

#### Extending the classes of the test cases.

The next example shown severals useful methods with wich we can to extend the classes of the own test cases from the implementation of them.

```php
<?php

testCase('', function () {
    useTrait('My\OwnTrait', [
        'myTraitMethod as protected',
    ]);

    property('myProperty');

    staticProperty('myStaticProperty', 100);

    method('sum', function (int $a, int $b): int {
        return $a + $b;
    });

    staticMethod('rest', function (int $a, int $b): int {
        return $a - $b;
    });

    test(function () {
        $this->assertNull($this->myProperty);
        $this->assertEquals(100, static::$myStaticProperty);
        $this->assertEquals(10, $this->sum(5, 5));
        $this->assertEquals(2, static::rest(4, 2));
    });
});
```

The PyramidalTests's functionment it is based in create dinamically the classes of the test cases wich PHPUnit will executed later. The calls to the `testCase()` function will returns a new instance of the class `ThenLabs\PyramidalTests\Model\TestCaseMode` wich will contains an instance of the class `ThenLabs\ClassBuilder\ClassBuilder` that will be wich finally will build the class in question.

By the earlier commented we can say that the more complete way the we have to customize the class of a test case, is across of his class builder.

The next example shown the way to specify the `@group` annotation on the class and a method.

>Check the project API [ClassBuilder](https://github.com/thenlabs/class-builder) for know all the posibilities of them.

```php
<?php

testCase(function () {
    test(function () {
        // ...
    })->getMethodBuilder()
        ->addComment('@group group2')
    ;
})->getClassBuilder()
    ->addComment('@group group1')
;
```

#### Using macros.

With the objetive to easier the reutilization of certain tests and cases is becouse exists the denominated *macros*.

In the next example has been created a macro wich contains a test that depends of the `myProperty` context property. Can be seen that it property is reutilized in two test cases wich have the `myProperty` property but created in different ways.

```php
<?php

macro('my macro', function () {
    test(function () {
        $this->assertTrue($this->myProperty);
    });
});

testCase(function () {
    property('myProperty', true);

    useMacro('my macro');
});

testCase(function () {
    setUp(function () {
        $this->myProperty = true;
    });

    useMacro('my macro');
});
```

Also it's important to comment that the macros may be created within the test cases and also may be extended in the next way:

```php
<?php

testCase(function () {
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

#### Commenting the setUpBeforeClassOnce() and tearDownAfterClassOnce() functions.

In addition to the `setUpBeforeClass()` and `tearDownAfterClass()` functions, there are also the `setUpBeforeClassOnce()` and `tearDownAfterClassOnce()` functions. The difference between the two is that with the latter, the respective linked anonymous functions will only be executed once.

## Executing PyramidalTests.

Like was sayed earlier, to execute the tests will be necessary to invoke the `./vendor/bin/pyramidal` command. That command perform practically equal to `./vendor/bin/phpunit` and that have some particularities. For example, when it's desired to execute one or more tests filtering by his title, it be execute the command in the next way:

    $ ./vendor/bin/pyramidal --filter="my title"

## Configurations.

Create a `pyramidal.yaml` file in the directory of your project and specify the desired configuration. That file will be parsed by the `./vendor/bin/pyramidal` command in addition of the classic `phpunit.xml`.

Nextly we showns the defaults options:

```yaml
pyramidal:
    file_pattern: 'test-*.php'
```