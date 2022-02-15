# El comando pyramidal.

Una vez que hayamos realizado la instalación deberemos ejecutar el comando `./vendor/bin/pyramidal` para ejecutar las pruebas que [PyramidalTests][PyramidalTests] deberá ejecutar.

Es muy importante comentar que este comando no es más que una envoltura de `./vendor/bin/phpunit` y es que como comentamos en la [introducción](introduction.md), [PyramidalTests][PyramidalTests] está construído sobre [PHPUnit][PHPUnit] por lo que mantiene **TODAS** las opciones y filosofía del mismo. Esto lo podría comprobar si especifica el argumento `--help` donde se mostrarán en pantalla exactamente las mismas opciones.

Las funciones principales que hace este comando son las siguientes:

1. Analizar el archivo `phpunit.xml` del proyecto.
2. En caso de que exista, analizar el archivo `pyramidal.yaml` el cual se usa para especificar [opciones de configuración de PyramidalTests](config-options.md).
3. Ejecutar los archivos `.php` que se encuentren en los directorios de las suites definidas en este archivo, y cuyos nombres comiencen por `test`(configurable).
4. Crear en memoria las clases de los casos de prueba.
5. Ejecutar dichas clases con el propio ejecutor de pruebas de [PHPUnit][PHPUnit].

---

<span class="float-start">Anterior: [Instalación](installation.md)</span>
<span class="float-end">Siguiente: [Creando las pruebas](creating-the-tests.md)</span>

[PyramidalTests]: https://pyramidal-tests.thenlabs.org
[Composer]: https://getcomposer.org/
[PHPUnit]: https://phpunit.de/