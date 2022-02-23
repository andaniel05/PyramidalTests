# Trabajando con decoradores.

Hasta ahora hemos visto que las implementaciones de pruebas y casos se realizan mediante el uso de funciones [cierre](https://www.php.net/manual/es/functions.anonymous.php), pero de manera alternativa, también es posible realizar ciertas implementaciones mediante llamadas sucesivas a los modelos.

>Los modelos son los valores devueltos por las funciones usadas para crear las pruebas y los casos. En el caso de `testCase()` y `describe()` devuelven un objeto de tipo `ThenLabs\PyramidalTests\Model\TestCaseModel` mientras que `test()` e `it()`, el objeto devuelto es de tipo `ThenLabs\PyramidalTests\Model\TestModel`.

## Creando pruebas con decoradores.

El siguiente ejemplo muestra una prueba que ha sido creada sin especificar una función [cierre][closure](y ni siquiera un título). En cambio se ha llamado directamente a `assertTrue(true)` sobre el modelo y de esta manera se ha llevado a cabo su implementación.

```php
<?php

test()->assertTrue(true);
```

>Esta manera de crear pruebas está inspirada en las [pruebas de orden superior de Pest](https://pestphp.com/docs/higher-order-tests).

Estas implementaciones pueden contener múltiples decoradores y estos se corresponden con los **métodos no estáticos** de la clase del caso en cuestión.

```php
<?php

class MyTestCase extends \PHPUnit\Framework\TestCase
{
    public function doAnything()
    {
        // ...
    }
}

setTestCaseClass(MyTestCase::class);

test('my title')
    ->doAnything()
    ->assertSame(50, 50);
```

En las situaciones donde ciertos métodos devuelvan objetos, es posible usar también los métodos de esos objetos como decoradores. El siguiente ejemplo se corresponde con una prueba de un proyecto [Laravel][Laravel] donde el método `get()` devuelve un objeto que contiene el método `assertStatus()`.

```php
<?php

setTestCaseClass('Tests\TestCase');

test('test title')
    ->get('/')
    ->assertStatus(200)
;
```

Si en estos casos se necesita regresar al contexto anterior, recomendamos que use márgenes y llame al decorador `end()` tal y como se hace en el siguiente ejemplo:

```php
<?php

setTestCaseClass('Tests\TestCase');

test('test title')
    ->get('/')
        ->assertStatus(200)
    ->end()
    ->assertTrue(true)
;
```

## Creando casos de prueba con decoradores.

De manera muy similar a las pruebas, también es posible hacer uso de esta característica en los casos aunque con algunas diferencias.

Lo primero que hay que comentar es que los decoradores serán ejecutados en el método `setUpBeforeClass()` del caso, y por tanto, solo se pueden emplear con los **métodos estáticos** de la clase en cuestión. Ejemplo:

```php
<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;

class MyTestCase extends \PHPUnit\Framework\TestCase
{
    public static function launchBrowser(): RemoteWebDriver
    {
        return RemoteWebDriver::create(/*...*/);
    }
}

setTestCaseClass(MyTestCase::class);

testCase('my title')
    ->launchBrowser()
        ->get('https://thenlabs.org')
    ->end()
    ->test('title of test1', function () {
        // ...
    })
    ->test('title of test2', function () {
        // ...
    })
;
```


---

<span class="float-start">Anterior: [Creando pruebas de extremo a extremo](end-to-end.md)</span>
<span class="float-end">Siguiente: [Opciones de configuración](config-options.md)</span>

[PHPUnit]: https://phpunit.de/
[Pest]: https://pestphp.com/
[Laravel]: https://laravel.com/
[PyramidalTests]: https://pyramidal-tests.thenlabs.org
[closure]: https://www.php.net/manual/es/functions.anonymous.php