
# Introducción.

[PyramidalTests][PyramidalTests] es un framework de pruebas basado en [PHPUnit][PHPUnit] que permite que se puedan crear las pruebas con el empleo de funciones anónimas anidadas. Este método tiene algunas ventajas frente al método tradicional de [PHPUnit][PHPUnit] el cual exige que se tenga que crear una clase por cada caso.

La principal ventaja que ofrece esta alternativa consiste en que las pruebas se crean con menos esfuerzo dado que se que escribe menos código, y además, se facilita la reutilización del mismo dado que por defecto, los casos anidados heredarán funcionalidades de sus padres.

Otra de las ventajas que ofrece este método consiste en que las pruebas se pueden describir con un lenguaje natural, lo cual posibilita que con leer el resultado de la ejecución, se pueda comprender mejor el funcionamiento del [sistema bajo pruebas][SUT], aunque esto dependerá de lo explícito que logre y/o desee ser el desarrollador al contar en una historia lo que se está probando.

Para demostrar un poco lo comentado anteriormente veamos el siguiente ejemplo donde en un único archivo se han creado tres pruebas pertenecientes a dos casos.

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

Una vez que se ejecuten dichas pruebas(lo cual se hace con el siguiente comando):

    $ ./vendor/bin/pyramidal --testdox

Se obtendría el siguiente resultado:

<pre class="text-white p-2">
<font color="#C4A000"><b>PyramidalTests 2.x.x</b></font> by Andy Daniel Navarro Taño and contributors.
PHPUnit 9.5.11 by Sebastian Bergmann and contributors.

<u style="text-decoration-style:single">it is created a product</u>
<font color="#4E9A06">✔</font> the product has not name
<font color="#4E9A06">✔</font> not contains categories

    <u style="text-decoration-style:single">adds a category to the product</u>
    <font color="#4E9A06">✔</font> the product contains the category

Time: 00:00.009, Memory: 6.00 MB

<span style="background-color:#4E9A06"><font color="#2E3436">OK (3 tests, 3 assertions)</font></span>
</pre>

Como puede verse, el resultado de la ejecución contiene los mismos títulos y márgenes del código fuente, y dado que se ha empleado un lenguaje natural, se puede decir que se está contando una historia del funcionamiento del [sistema bajo pruebas][SUT].

Este ejemplo tan sencillo muestra los beneficios que [PyramidalTests][PyramidalTests] ofrece a la hora de trabajar con [PHPUnit][PHPUnit]. Decimos esto porque [PyramidalTests][PyramidalTests] está basado 100% en [PHPUnit][PHPUnit] y se podría decir que es como una extensión(o framework) del mismo que lo que hace es extender sus posibilidades.

Si solo estuviéramos usando [PHPUnit][PHPUnit] y quisiéramos obtener el mismo resultado anterior de entrada esto no sería posible ya que por defecto este no muestra márgenes en los resultados. Además, la reutilización del código sería todo un reto porque no bastaría con solo usar la herencia entre las clases de los casos ya que los métodos cuyo nombre cuentan con el prefijo *test* también se estarían heredando y esto implicaría que el segundo caso contendría tres pruebas y no solo una como ocurre en el ejemplo.

Lo que hemos mostrado aquí no son solo las únicas bondades que ofrece [PyramidalTests][PyramidalTests] y lo exhortamos a seguir leyendo para que conozca más al respecto.

[PHPUnit]: https://phpunit.de/
[SUT]: https://en.wikipedia.org/wiki/System_under_test
[PyramidalTests]: https://pyramidal-tests.thenlabs.org