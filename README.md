
<h1 align="center">PyramidalTests</h1>

### A complementary framework for PHPUnit that focuses on simplicity, reusability, and storytelling.

#### If you like this project gift us a ⭐.

<hr />

## Documentation.

- [English](doc/en/index.md)
- [Español](doc/es/index.md)

## Installation.

    $ composer require --dev thenlabs/pyramidal-tests 2.0.x-dev

>This project is still under development.

## Example:

```php
<?php
// tests/test-it-is-created-a-product.php

testCase('it is created a product', function () {
    setUp(function () {
        $this->product = new Product();
    });

    test('the product has not name', function () {
        $this->assertEmpty($this->product->getName());
    });

    test('not contains categories', function () {
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

## Executing:

    $ ./vendor/bin/pyramidal --testdox

>The `--testdox` argument is optional.

## Result:

<pre class="text-white p-2">
<font color="#C4A000"><b>PyramidalTests 2.x.x</b></font> by Andy Daniel Navarro Taño and contributors.
PHPUnit 9.5.x by Sebastian Bergmann and contributors.

<u style="text-decoration-style:single">it is created a product</u>
<font color="#4E9A06">✔</font> the product has not name
<font color="#4E9A06">✔</font> not contains categories

    <u style="text-decoration-style:single">adds a category to the product</u>
    <font color="#4E9A06">✔</font> the product contains the category

Time: 00:00.009, Memory: 6.00 MB

<span style="background-color:#4E9A06"><font color="#2E3436">OK (3 tests, 3 assertions)</font></span>
</pre>

## Development.

### Running the tests.

    $ composer test
