# Creando pruebas de extremo a extremo.

A veces necesitamos crear ciertas pruebas que son muy costosas en tiempo y/o recursos dado que implican la interacción con otros procesos del sistema, como por ejemplo, abrir una nueva instancia de un navegador web, etc.

Con el objetivo de crear este tipo de pruebas es que existen las funciones `setUpBeforeClassOnce()` y `tearDownAfterClassOnce()`.

>De manera equivalente también existen las funciones `beforeAllOnce()` y `afterAllOnce()`.

El ejemplo que se muestra a continuación consiste en una prueba de extremo a extremo que lo que hace es abrir una instancia de [Google Chrome](https://en.wikipedia.org/wiki/Google_Chrome), visitar el sitio de [Wikipedia](https://www.wikipedia.com), escribir "Cuba" en el buscador y presionar enter. A medida que se va avanzando entre los diferentes pasos se van probando cosas como los títulos de las páginas, etc.

>Para la interacción con el navegador se está utilizando [php-webdriver](https://github.com/php-webdriver/php-webdriver).

Como se puede ver, tanto en el caso externo como en el interno, se hace uso de la función `setUpBeforeClassOnce()` la cual sirve para indicar que ese código se ejecutará una única vez independientemente de la cantidad de casos internos que existan.

Dicho de una manera más clara, cuando este archivo de pruebas se ejecute, se abrirá una única instancia del navegador y se llevarán a cabo los pasos antes comentados. En cambio, si solo se estuvieran utilizando las funciones `setUpBeforeClass`, esto implicaría que se abrieran dos instancias del navegador pues a la hora de ejecutar el caso más interno se estaría heredando el código del más externo tal y como hemos visto hasta ahora.

```php
<?php
// tests/test-end-to-end-example.php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverExpectedCondition;

testCase('open https://www.wikipedia.com', function () {
    staticProperty('driver');
    staticProperty('searchInput');

    setUpBeforeClassOnce(function () {
        static::$driver = RemoteWebDriver::create(
            'http://localhost:4444',
            DesiredCapabilities::chrome()
        );

        static::$driver->get('https://www.wikipedia.com');
    });

    test('the title contains "Wikipedia"', function () {
        $this->assertStringContainsString('Wikipedia', static::$driver->getTitle());
    });

    test('contains the search input', function () {
        static::$searchInput = static::$driver->findElement(
            WebDriverBy::id('searchInput')
        );

        $this->assertInstanceOf(RemoteWebElement::class, static::$searchInput);
    });

    testCase('type "Cuba" and press enter', function () {
        setUpBeforeClassOnce(function () {
            static::$searchInput->sendKeys("Cuba\n");

            static::$driver->wait()->until(
                WebDriverExpectedCondition::titleContains('Cuba')
            );
        });

        test('the "Cuba" page is opened', function () {
            $this->assertStringEndsWith(
                'wikipedia.org/wiki/Cuba',
                static::$driver->getCurrentURL()
            );
        });
    });
});
```

Seguidamente se muestra el resultado de la ejecución del ejemplo. Como comentamos anteriormente, toda la ejecución se llevó a cabo con una única instancia del navegador, a pesar de que el resultado muestra dos casos de prueba.

<pre class="text-white p-2">$ ./vendor/bin/pyramidal --testdox
<font color="#C4A000"><b>PyramidalTests 2.x.x</b></font> by Andy Daniel Navarro Taño and contributors.
PHPUnit 9.5.x by Sebastian Bergmann and contributors.

<u style="text-decoration-style:single">open https://www.wikipedia.com</u>
 <font color="#4E9A06">✔</font> the title contains &quot;Wikipedia&quot;
 <font color="#4E9A06">✔</font> contains the search input

    <u style="text-decoration-style:single">type &quot;Cuba&quot; and press enter</u>
     <font color="#4E9A06">✔</font> the &quot;Cuba&quot; page is opened

Time: 00:32.058, Memory: 6.00 MB

<span style="background-color:#4E9A06"><font color="#2E3436">OK (3 tests, 3 assertions)</font></span>
</pre>

---

<span class="float-start">Anterior: [Creando las pruebas](creating-the-tests.md)</span>
<span class="float-end">Siguiente: [Trabajando con decoradores](decorators.md)</span>