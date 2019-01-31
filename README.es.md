# PyramidalTests

[View in english](README.md)

PyramidalTests es una extensión de [PHPUnit](https://phpunit.de/) que permite crear casos de prueba mediante el empleo de funciones anónimas. Su filosofía  está inspirada en proyectos como [mochajs][1], [jasmine][2], [peridotphp][3], etc.

[1]: https://mochajs.org/
[2]: https://jasmine.github.io/
[3]: http://peridot-php.github.io/

>Para comprender el trabajo con esta extensión es necesario que usted tenga conocimientos sobre [PHPUnit](https://phpunit.de/) y de pruebas de software en general, donde lo ideal sería que además tuviese conocimientos sobre alguno de los frameworks antes mencionados.

Su principal objetivo consiste en extender las aplicaciones de [PHPUnit](https://phpunit.de/) al campo [BDD](https://en.wikipedia.org/wiki/Behavior-driven_development). Además de esto, otro de sus objetivos consiste en facilitar la creación de casos de prueba ofreciendo ciertas ventajas como la reutilización de pruebas.

>Para una rápida toma de contacto usted puede clonar y modificar el proyecto [PyramidalTests-Demo](https://github.com/andaniel05/PyramidalTests-Demo). El mismo contiene ya implementado todas las funcionalidades que aquí se muestran.

## Instalación.

### 1. Instalando con Composer.

    $ composer require andaniel05/pyramidaltests dev-master

### 2. Declarando la extensión en el archivo de configuración.

Agregue la siguiente declaración al archivo `phpunit.xml` o equivalente.

```xml
    <extensions>
        <extension class="Andaniel05\PyramidalTests\Extension"></extension>
    </extensions>
```

### 3. Declarando el DSL a usar.

En el archivo `bootstrap.php` o equivalente de su proyecto incluya una referencia al archivo `/vendor/andaniel05/pyramidaltests/src/DSL/PHPUnit.php`.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/andaniel05/pyramidaltests/src/DSL/PHPUnit.php'; // DSL

// ...
```

>Si desea crear un DSL personalizado puede tomar este archivo como muestra.

## Conociendo la filosofía.

El siguiente fragmento de código se corresponde con un archivo de pruebas donde se muestra la filosofía de desarrollo.

```php

setTestCaseNamespace('Andaniel05\PyramidalTests\Demo\Tests');
setTestCaseClass('PHPUnit\Framework\TestCase');

use Andaniel05\PyramidalTests\Demo\Product;
use Andaniel05\PyramidalTests\Demo\Category;

testCase('create a product', function () {
    setUp(function () {
        $this->product = new Product;
    });

    test('by default the product has a code', function () {
        $this->assertNotEmpty($this->product->getCode());
    });

    test('by default the product not contains categories', function () {
        $this->assertCount(0, $this->product->getCategories());
    });

    testCase('the product adds a category', function () {
        setUp(function () {
            $this->category = new Category;
            $this->product->addCategory($this->category);
        });

        test('the product contains the category', function () {
            $this->assertContains($this->category, $this->product->getCategories());
        });
    });
});
```

Como puede deducir se han creado dos casos de prueba para probar el funcionamiento de la clase `Product`.

Puede ver que los mismos se han creado mediante la función `testCase()` donde su primer argumento se corresponde con la descripción en lenguaje natural y el segundo con una función anónima desde donde se definen los miembros del respectivo caso de prueba.

De igual manera a como en el ejemplo se está usando la función `setUp()`, es posible emplear además las funciones `setUpBeforeClass()`, `tearDown()` y `tearDownAfterClass()` donde con cada una de ellas se define el respectivo comportamiento del correspondiente estado.

Para la creación de las pruebas se emplea la función `test()`, donde en este caso, al igual que la función `testCase()` también recibe como primer argumento una descripción y como segundo la función anónima con el código de la prueba.

Una de las características más importantes y potentes de la extensión es la anidación de casos de prueba al llamar a la función `testCase()` desde dentro de la declaración de otro. De esta forma lo que se hace es declarar que el más interno hereda toda la funcionalidad del más externo pero es muy importante mencionar que **las pruebas no se heredan**.

Si no desea que alguna de las cuatro funciones de estado herede el comportamiento del padre entonces se debe especificar la palabra `false` como segundo argumento. Ejemplo:

```php
testCase(function () {
    setUp(function () {
        $this->name = 'Andy';
    });

    testCase(function () {
        setUp(function () {
            $this->lastname = 'Navarro';
        }, false);

        test(function () {
            $this->assertFalse(isset($this->name));
            $this->assertEquals('Navarro', $this->lastname);
        });
    });
});
```

Como puede ver también en el ejemplo anterior, las descripciones en las funciones `testCase()` y `test()` son totalmente opcionales. Si se omiten entonces se estarán creando pruebas y/o casos **anónimos**.

Es totalmente recomendable que cada archivo de pruebas defina el espacio de nombres que deben tener los casos de prueba definidos en el mismo, así como su clase base. Estas declaraciones se hacen con las funciones `setTestCaseNamespace()` y `setTestCaseClass()` tal y como puede ver en el primer ejemplo.

## Ejecutando las pruebas.

Una vez que [PHPUnit](https://phpunit.de/) sea invocado se ejecutarán todas las pruebas definidas de esta manera tal y como se muestra en la siguiente imagen.

    $ ./vendor/bin/phpunit --testdox

![](full_results.png)

Como puede ver existe un bloque de texto que indica que la sección superior se corresponde con el resultado de la ejecución de las pruebas de la extensión, mientras que la inferior con el resto de pruebas escritas en la manera tradicional de [PHPUnit](https://phpunit.de/).

>Queremos comentar que el motivo de existencia de esta división se debe a que no hemos encontrado ningún evento en la arquitectura de [PHPUnit](https://phpunit.de/) que permita agregar pruebas en el `TestRunner` por defecto.

De esta manera, se cumple con el principal objetivo de la extensión que es el de ampliar las posibilidades del framework ya que las pruebas se escriben con un nuevo estilo y pueden coexistir con las tradicionales.

## Conociendo las opciones de configuración.

### Ejecutando solo las pruebas de la extensión.

Si en su proyecto pretende escribir TODAS las pruebas al estilo de la extensión entonces en su archivo de configuración usted debe agregar la siguiente variable de entorno:

```xml
<php>
    <env name="PYRAMIDAL_ONLY" value="1"></env>
</php>
```

De esta forma cuando [PHPUnit](https://phpunit.de/) sea ejecutado el resultado se mostrará de la siguiente manera.

![](results.png)

### Especificando los márgenes de los casos de pruebas anidados.

Cuando PHPUnit es ejecutado con la opción `--testdox`, los casos de prueba anidados son mostrados con un márgen por defecto de 4 espacios. Si se desea especificar el valor de este márgen entonces se debe declarar la variable de entorno `PYRAMIDAL_MARGIN` cuyo valor se corresponderá con la cantidad de espacios deseados.

```xml
<php>
    <env name="PYRAMIDAL_MARGIN" value="8"></env>
</php>
```
