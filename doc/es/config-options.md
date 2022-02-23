# Opciones de configuración.

Seguidamente se muestran todas las opciones de configuración que puede contener el archivo `pyramidal.yaml` así como sus valores por defecto.

```yaml
pyramidal:
    dsl: ['phpunit', 'describe-it']
    file_pattern: '^test.*\.php$'
    colors: true
    testdox: false
```

`dsl` sirve para especificar la familia de funciones que utilizará para crear sus pruebas. En el caso de que se desee crear nuevas funciones se deberá especificar en esta opción la ruta al archivo.

`file_pattern` contiene la expresión regular que se utiliza para cargar sus archivos de prueba. Puede ver que por defecto se cargan los archivos `.php` cuyos nombres comiencen por `test`.

`colors` sirve para especificar si se utilizará la opción `--colors` del ejecutor de [PHPUnit][PHPUnit].

`testdox` sirve para indicar si por defecto se usará la opción `--testdox` del ejecutor de [PHPUnit][PHPUnit]. Si esta opción vale `true` la llamada a `./vendor/bin/pyramidal` será equivalente a `./vendor/bin/pyramidal --testdox`.

---

<span class="float-start">Anterior: [Trabajando con decoradores.](decorators.md)</span>

[PHPUnit]: https://phpunit.de/