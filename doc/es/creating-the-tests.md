
# Creando las pruebas.

Como se comentó anteriormente, [PyramidalTests][PyramidalTests] se basa en construir de manera dinámica las clases que finalmente serán ejecutadas por [PHPUnit][PHPUnit].

>La construcción dinámica de las clases se logra gracias al proyecto [ClassBuilder][ClassBuilder].

Las clases y sus miembros se van definiendo con llamadas a funciones cuyos nombres se corresponden con los métodos claves propios de [PHPUnit][PHPUnit]. Por ejemplo:

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

Del código anterior se puede deducir fácilmente lo que define cada una de las funciones que se emplean. El mismo estaría generando una clase equivalente a la siguiente:

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

>Es muy importante aclarar que en ningún momento el usuario interactúa con la clase generada, de hecho, este es un proceso totalmente transparente al mismo. Este ejemplo solo lo ponemos para que se comprenda mejor el uso de las funciones.

## Definiendo miembros a las clases.

Cuando se trabaja con [PHPUnit][PHPUnit] suele ser frecuente que a las clases se le agreguen nuevos métodos y propiedades. Esto también es posible de hacer empleando las siguiens funciones:

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

Estas funciones que sirven para crear métodos y propiedades devuelven objetos del tipo [Method](https://github.com/thenlabs/class-builder/blob/1.0/src/Model/Method.php) o [Property](https://github.com/thenlabs/class-builder/blob/1.0/src/Model/Property.php) según sea el caso, los cuales son objetos tipo `Builder` y gracias a los mismos usted podrá personalizar cualquier aspecto del miembro. Por ejemplo, en caso de que se desee agregar una anotación en el bloque de comentarios se podría hacer de la siguiente manera:

```php
<?php

testCase('title of the test case', function () {
    method('sum', function () {
        // ...
    })->addComment('@group group1');

    // ...
});
```

De manera general y a modo de resumen, es posible personalizar **TODO** lo que se necesite gracias al proyecto [ClassBuilder][ClassBuilder].

### Trabajando con proveedores de datos.

De esta misma manera podríamos definir y usar proveedores de datos:

Gracias al método `with(array $data)` es posible crear tests con proveedores de datos.

```php
<?php

test('a test with data provider', function ($a, $b, $result) {
    $this->assertSame($result, $a + $b);
})->with([
    [1, 1, 2],
    [2, 2, 4],
]);
```

## Definiendo la clase base de los casos de prueba.

La clase base por defecto es `PHPUnit\Framework\TestCase` pero cuando se necesite utilizar otra es posible hacerlo de la siguiente manera:

```php
<?php

setTestCaseClass(MyCustomTestCase::class);

testCase('title of the test case', function () {
    // ...
});
```

## Anidación y reutilización de casos de prueba.

Como se ha comentado anteriormente una de las mejores ventajas que ofrece [PyramidalTests][PyramidalTests] es que permite anidar casos de prueba lo cual posibilita la reutilización del código.

Cuando se anidan dos o más casos las clases de los más internos heredan desde las clases de los más externos pero hay que tener en cuenta que **las pruebas no son heredadas**.

El siguiente ejemplo muestra que en la prueba se está utilizando una propiedad definida en el método `setUp()` del caso padre.

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

Esto ocurre porque cada método clave de [PHPUnit][PHPUnit](`setUp` en este caso) invoca al padre antes de su ejecución, pero esto puede ser desactivado indicando `false` como segundo argumento en la llamada a dicha función. Ejemplo:

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

## Trabajando con macros.

Con el objetivo de facilitar la reutilización de ciertas pruebas y casos es que exiten las denominadas *macros*.

En el siguiente ejemplo se ha creado una macro la cual contiene una prueba que depende de la propiedad `myProperty` del contexto. Puede verse que dicha prueba es reutilizada en dos casos que poseen la propiedad `myProperty` pero creadas de diferentes maneras.

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

También es importante mencionar que las macros se pueden crear dentro de los casos de prueba y que además pueden ser extendidos de la siguiente manera:

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

## Trabajando con pruebas y casos sin títulos.

Hasta este momento siempre hemos visto que a las llamadas a las funciones `testCase()` y `test()` siempre se les especifica un título como su primer argumento. Algo que puede resultar útil en determinados casos es el hecho de obviar esos títulos y en su lugar centrarse directamente en la implementación del código. Ejemplo:

```php
<?php
// tests/test-example.php

testCase(function () {
    test(function () {
        $this->assertTrue(true);
    });
});
```

Si a la hora de ejecutar esas pruebas lo hiciéramos especificando la opción `--testdox` se mostraría un resultado como el siguiente:

<pre class="text-white p-2"><font color="#C4A000"><b>PyramidalTests 2.x.x</b></font> by Andy Daniel Navarro Taño and contributors.
PHPUnit 9.5.x by Sebastian Bergmann and contributors.

<u style="text-decoration-style:single">tests/test-example.php</u>
 <font color="#4E9A06">✔</font> test1 :5

Time: 00:00.004, Memory: 6.00 MB

<span style="background-color:#4E9A06"><font color="#2E3436">OK (1 test, 1 assertion)</font></span></pre>

Como puede verse, en estos casos se mostrará como título del caso de prueba la ruta relativa del archivo del mismo, y como título para las pruebas se usará el prefijo `test` seguido del número que le corresponda y por último, se mostrará el número de la línea donde se ha llamado a la función `test()`.

### Creando pruebas sin definir un caso padre.

Un resultado similar al anterior se podría obtener obviando la llamada a `testCase()` tal y como muestra el siguiente ejemplo. Esto puede ser útil en archivos donde solo contengan un único caso.

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

## Mostrando otras formas de crear las pruebas.

El estilo que hemos mostrando hasta utiliza funciones cuyos nombres se corresponden con funciones clave en [PHPUnit][PHPUnit]. Pero adicionalmente, se encuentran disponibles otras funciones cuyos nombres constrituyen otro estilo bastante popular en otros proyectos y preferido por muchos desarrolladores.

El siguiente fragmento de código es totalmente equivalente al mostrado en el primer ejemplo de este documento.

### Describe-It.

```php
<?php

describe('my custom title', function () {
    beforeAll(function () {
        // ...
    });

    beforeEach(function () {
        // ...
    });

    it('title of the test', function () {
        // ...
    });

    afterEach(function () {
        // ...
    });

    afterAll(function () {
        // ...
    });
});
```

Hemos visto que este estilo se ha venido popularizando en la comunidad [PHP][PHP] gracias al excelente *framework* [Pest][Pest]. Una de las características que más prefieren los usuarios del mismo es la de emplear expectativas en vez de los aciertos propios de [PHPUnit][PHPUnit].

#### Usando expectativas en vez de aciertos.

Si quisiéramos hacer uso de esta característica primeramente tendríamos que instalar el paquete [pest-plugin-expectations](https://github.com/pestphp/pest-plugin-expectations):

```text
$ composer require pestphp/pest-plugin-expectations --dev
```

Y de esta manera podríamos hacer uso de los mismos:

```php
<?php

test(function () {
    expect(true)->toBe(true); // Pest expectations.
});
```

### Usando decoradores.

Los decoradores ofrecen a los usuarios formas simplicadas de hacer cosas. Más adelante vamos a abordar más en profundidad este tema, pero por ahora queremos mostrar que gracias a los mismos existe otra manera de crear las pruebas tal y como mostramos seguidamente:

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

<span class="float-start">Anterior: [El comando pyramidal](the-pyramidal-command.md)</span>
<span class="float-end">Siguiente: [Creando pruebas End-to-End](end-to-end.md)</span>

[Pest]: https://pestphp.com/
[PHP]: https://www.php.net/
[PHPUnit]: https://phpunit.de/
[ClassBuilder]: https://github.com/thenlabs/class-builder
[PyramidalTests]: https://pyramidal-tests.thenlabs.org