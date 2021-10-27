
# PyramidalTests

Extends the PHPUnit possibilities to the BDD field.

>If you like this project gift us a ⭐.

## Documentation.

- [🇬🇧 English](https://thenlabs.org/en/doc/pyramidal-tests/2.0/index.html)
- [🇪🇸 Español](https://thenlabs.org/es/doc/pyramidal-tests/2.0/index.html)

## Installation.

    $ composer require thenlabs/pyramidal-tests 2.0.x-dev

>Require PHPUnit >= 8.5

## Example:

```php
<?php
// tests/test-it-is-created-a-product.php

use ThenLabs\PyramidalTests\Demo\Product;
use ThenLabs\PyramidalTests\Demo\Category;

testCase('it is created a product', function () {
    setUp(function () {
        $this->product = new Product;
    });

    test('the product has a #CODE#', function () {
        $code = $this->product->getCode();

        $this->assertMatchesRegularExpression('/#\d+#/', $code);
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

## Executing.

    $ ./vendor/bin/pyramidal

Demo:

![](demo.gif)

## Development.

### Running the tests.

For run the tests with differents versions of PHPUnit run:

    $ docker run --rm -v $(pwd):/app jitesoft/phpunit:{php_version}-{phpunit_version}

See [https://hub.docker.com/r/jitesoft/phpunit](https://hub.docker.com/r/jitesoft/phpunit) for get the values for `php_version` and `phpunit_version`.
