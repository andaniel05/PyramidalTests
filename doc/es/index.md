
# PyramidalTests

## Introducción.

PyramidalTests es un framework de pruebas basado en PHPUnit que permite que se puedan crear las pruebas con el empleo de funciones anónimas anidadas. Este método tiene algunas ventajas frente al método tradicional de PHPUnit que exige que se tenga que crear una clase por cada caso.

La principal ventaja está en que las pruebas se crean mucho más rápido dado que se que escribe mucho menos código, y además, se facilita la reutilización del mismo dado que por defecto, los casos anidados heredarán de sus padres.

Otra de las ventajas que ofrece este método consiste en que las pruebas se podrán describir con un lenguaje natural, lo cual facilitará que con leer el resultado de la ejecución, se pueda comprender mejor el funcionamiento del sistema bajo pruebas(SUT), aunque esto dependerá de lo explícito que logre y/o desee ser el desarrollador al contar en una historia lo que se está probando.

Ejemplo 1:

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

Con el ejemplo anterior pretenemos destacar varios aspectos.

El primero de ellos consiste en la forma de crear las pruebas. Puede verse que con funciones del tipo `testCase()`, `test()`, `setUp()`, etc, se implementa de una manera muy simple toda la lógica del caso de prueba en cuestión y se describe empleando un lenguaje natural.

Además de esto, puede verse que se dispone de las utilidades propias de PHPUnit como es el caso de los métodos para crear aciertos, *mocks*, etc y es que por defecto, los casos de pruebas heredan de la clase `PHPUnit\Framework\TestCase`.

Por último, queremos destacar que los casos más internos heredan de los más externos lo cual facilita considerablemente la reutilización de código.

## Instalación.

    $ composer require --dev thenlabs/pyramidal-tests 2.0.x-dev

>Tenga en cuenta que actualmente no se cuenta con una versión estable.

## Trabajando con PyramidalTests.

PyramidalTests está construído sobre la base de PHPUnit por lo que su filosofía de trabajo es prácticamente la misma.

Una vez que PyramidalTests se encuentre instalado en un proyecto, el mismo podrá ser ejecutado con el comando `./vendor/bin/pyramidal`.

Puede comprobarse que este comando presenta las mismas opciones que el original de PHPUnit por lo que todos los conocimientos que usted tenga sobre PHPUnit son aplicables también en el trabajo con PyramidalTests. Solo existirán algunas particularidades que mencionaremos a más adelante.

### Creando las pruebas.

Las pruebas que se creen con PyramidalTests se deben almacenar en archivos cuyos nombres deben cumplir con el patrón `test-*.php`.

El contenido de dichos archivos debe ser similar al que mostramos en el ejemplo 1 de esta guía. Como mostramos en el mismo, los casos de prueba se crearán con el empleo de las siguientes funciones:

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

#### Creando pruebas y casos sin títulos.

Queremos comentar que el darle un título a un caso de prueba o a una prueba es algo totalmente opcional y los mismos pueden ser creados de la siguiente manera:

```php
<?php

testCase(function () {
    test(function () {
        // ...
    });
});
```

Esto puede ser útil en ocasiones donde solo pensamos en código fuente y obviamos las descripciones en lenguaje natural.

>Esto también se puede lograr si especificamos una cadena vacía en el título, solo que esta es una manera más simplificada de lograrlo.

#### Creando pruebas sin especificar un caso padre.

En los archivos también es posible llamar a la función `test()` directamente desde la raíz y de esta forma se creará automáticamente un caso padre.

```php
<?php

test(function () {
    // ...
});
```

#### Especificando la clase de los casos de prueba.

Como dijimos anteriormente, por defecto todos los casos de prueba heredan de la clase `PHPUnit\Framework\TestCase`.

Cuando se necesite especificar otra clase para los mismos es posible hacerlo de la siguiente manera:

```php
<?php

setTestCaseClass('My\Own\TestCase');

testCase('', function () {
    // ...
});
```

#### Extendiendo las clases de los casos.

El siguiente ejemplo, muestra una serie de métodos utilitarios con los cuales podemos extender las clases de nuestros casos desde la propia implementación del mismo.

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

El funcionamiento de PyramidalTests se basa en crear de manera dinámica las clases de los casos de prueba que PHPUnit ejecutará más tarde. Las llamadas a la función `testCase()` devolverá una nueva instancia de la clase `ThenLabs\PyramidalTests\Model\TestCaseModel` la cual contendrá una instancia de la clase `ThenLabs\ClassBuilder\ClassBuilder` que será la que finalmente construirá la clase del caso en cuestión.

Por lo antes comentado podemos decir que la forma más completa que tenemos para personalizar la clase de un caso de pruebas, es a través de su *class builder*.

El siguiente ejemplo muestra la forma de especificar la anotación `@group` sobre la clase y un método

>Revise la API del proyecto [ClassBuilder](https://github.com/thenlabs/class-builder) para que conozca todas las posibilidades del mismo.

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

#### Usando macros.

Con el objetivo de facilitar la reutilización de ciertas pruebas y casos es que exiten las denominadas *macros*.

En el siguiente ejemplo se ha creado una macro la cual contiene una prueba que depende de la propiedad `myProperty` del contexto. Puede verse que dicha prueba es reutilizada en dos casos que poseen la propiedad `myProperty` pero creadas de diferentes maneras.

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

También es importante mencionar que las macros se pueden crear dentro de los casos de prueba y que además pueden ser extendidos de la siguiente manera:

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

#### Comentando las funciones setUpBeforeClassOnce() y tearDownAfterClassOnce().

Además de las funciones `setUpBeforeClass()` y `tearDownAfterClass()`, existen también las funciones `setUpBeforeClassOnce()` y `tearDownAfterClassOnce()`. La diferencia que existen entre ambas está en que con las segundas solo se ejecutarán una única vez las respectivas funciones anónimas vinculadas.

## Ejecutando PyramidalTests.

Como se dijo anteriormente, para ejecutar las pruebas será necesario invocar el comando `./vendor/bin/pyramidal`. Este comando funciona prácticamente igual que el comando `./vendor/bin/phpunit` y solo tendrá algunas particularidades. Por ejemplo, cuando se desee ejecutar una o varias pruebas filtrándolas por su título se deberá ejecutar dicho comando de la siguiente manera:

    $ ./vendor/bin/pyramidal --filter="my title"

## Configuraciones.

Cree un archivo `pyramidal.yaml` en el directorio de su proyecto y especifique la configuración deseada. Este archivo será interpretado por el comando `./vendor/bin/pyramidal`, además del típico `phpunit.xml`.

Seguidamente se muestran las opciones de configuración por defecto.

```yaml
pyramidal:
    file_pattern: 'test-*.php'
```