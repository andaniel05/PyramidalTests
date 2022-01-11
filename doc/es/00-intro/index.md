
# PyramidalTests

## Introducción.

PyramidalTests es un framework de pruebas basado en [PHPUnit][PHPUnit] que permite que se puedan crear las pruebas con el empleo de funciones anónimas anidadas. Este método tiene algunas ventajas frente al método tradicional de [PHPUnit][PHPUnit] el cual exige que se tenga que crear una clase por cada caso.

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

![](result.png)

Como puede verse, el resultado de la ejecución contiene los mismos títulos y márgenes del código fuente, y dado que se ha empleado un lenguaje natural, se puede decir que se está contando una historia del funcionamiento del [sistema bajo pruebas][SUT].

Este ejemplo tan sencillo muestra los beneficios que PyramidalTests ofrece a la hora de trabajar con [PHPUnit][PHPUnit]. Decimos esto porque PyramidalTests está basado 100% en [PHPUnit][PHPUnit] y se podría decir que es como extensión(o framework) del mismo que lo que hace es extender sus funcionalidades.

Si solo estuviéramos usando [PHPUnit][PHPUnit] y quisiéramos obtener el resultado anterior de entrada esto no sería posible por defecto este no muestra márgenes en los resultados. Además, la reutilización del código sería todo un reto porque no bastaría con solo usar la herencia entre las clases de los casos ya que los métodos cuyo nombre tienen el prefijo *test* también se estarían compartiendo entre ambas y esto implicaría que la segunda contendría tres pruebas y no solo una como ocurre en el ejemplo anterior.

Lo que hemos mostrado aquí no son solo las únicas bondades que ofrece PyramidalTests y lo exhortamos a seguir leyendo para que las conozca.

[PHPUnit]: https://phpunit.de/
[SUT]: https://en.wikipedia.org/wiki/System_under_test