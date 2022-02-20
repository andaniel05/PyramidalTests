
# Introduction.

[PyramidalTests][PyramidalTests] is a testing framework based on [PHPUnit][PHPUnit] that allows tests to be created using nested anonymous functions. This method has some advantages over the traditional [PHPUnit][PHPUnit] method which requires you to create a class for each case.

The main advantage offered by this alternative is that the tests are created with less effort since less code is written, and in addition, the reuse of the same is facilitated since by default, the nested cases will inherit functionalities from their parents.

Another advantage offered by this method is that the tests can be described in natural language, which makes it possible to better understand the operation of the [system under test][SUT] by reading the result of the execution, although this will depend on how explicit the developer manages and/or wishes to be in telling what is being tested in a story.

To demonstrate a little what was said above, let's see the following example where three tests belonging to two cases have been created in a single file.

```php
<?php
// tests/test-an-example.php

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

Once those tests are executed (which is done with the following command):

    $ ./vendor/bin/pyramidal --testdox

The following result would be obtained:

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

As can be seen, the result of the execution contains the same titles and margins as the source code, and since a natural language has been used, it can be said that a story of the operation of the [system under test][SUT] is being told.

Este ejemplo muy simple muestra los beneficios que ofrece [PyramidalTests][PyramidalTests] cuando se trabaja con [PHPUnit][PHPUnit]. Decimos esto porque [PyramidalTests][PyramidalTests] está basado 100% en [PHPUnit][PHPUnit] y se podría decir que es como una extensión (o framework) del mismo que lo que hace es ampliar sus posibilidades.

If we were just using [PHPUnit][PHPUnit] and wanted to get the same result as above, this would not be possible since by default it does not show margins in the results. In addition, code reuse would be a challenge because it would not be enough to just use inheritance between classes since the methods whose names begin with *test* would also be inheriting and this would imply that the second case would contain three tests and not just one as in the example.

Lo que hemos mostrado aquí no son solo los únicos beneficios que ofrece [PyramidalTests][PyramidalTests] y lo alentamos a que continúe leyendo para obtener más información al respecto.

---

<span class="float-end">Siguiente: [Installation](installation.md)</span>

[PHPUnit]: https://phpunit.de/
[SUT]: https://en.wikipedia.org/wiki/System_under_test
[PyramidalTests]: https://pyramidal-tests.thenlabs.org