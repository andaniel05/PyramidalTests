# Configuration options.

Below are all the configuration options that the `pyramidal.yaml` file can contain, as well as their default values.

```yaml
pyramidal:
    dsl: ['phpunit', 'describe-it']
    file_pattern: '^test.*\.php$'
    colors: true
    testdox: false
```

`dsl` is used to specify the family of functions that you will use to create your tests. If you want to create new functions, you must specify the path to the file in this option.

`file_pattern` contains the regular expression used to load your test files. You can see that by default `.php` files whose names start with `test` are loaded.

`colors` is used to specify whether to use the `--colors` option of the [PHPUnit][PHPUnit] executor.

`testdox` is used to indicate whether the `--testdox` option of the [PHPUnit][PHPUnit] executor will be used by default. If this option is `true` the call to `./vendor/bin/pyramidal` will be equivalent to `./vendor/bin/pyramidal --testdox`.

---

<span class="float-start">Back: [Working with decorators](decorators.md)</span>

[PHPUnit]: https://phpunit.de/