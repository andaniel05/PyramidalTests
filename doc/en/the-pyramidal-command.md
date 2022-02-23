# The pyramidal command.

Once we have done the installation we will have to execute the command `./vendor/bin/pyramidal` to execute the tests that [PyramidalTests][PyramidalTests] should execute.

It is very important to comment that this command is nothing more than a wrapper of `./vendor/bin/phpunit` and it is that as we commented in the [introduction](introduction.md), [PyramidalTests][PyramidalTests] is built on top of [PHPUnit ][PHPUnit] therefore maintains **ALL** the options and philosophy of it. This could be checked by specifying the `--help` argument where exactly the same options will be displayed.

The main functions of this command are the following:

1. Parse the project's `phpunit.xml` file.
2. If it exists, parse the `pyramidal.yaml` file which is used to specify [PyramidalTests configuration options](config-options.md).
3. Execute the `.php` files found in the directories of the suites defined in this file, and whose names start with `test`(configurable).
4. Create test case classes in memory.
5. Run those classes with [PHPUnit][PHPUnit]'s own test runner.

## Filtering tests in the execution.

In much the same way as [PHPUnit][PHPUnit], it is possible to run only certain tests and exclude the rest from execution.

```text
$ ./vendor/bin/pyramidal --filter="title of test(or test case)"
```

This way only tests and/or cases whose titles match the value specified to the `--filter` parameter will be executed.

---

<span class="float-start">Back: [Installation](installation.md)</span>
<span class="float-end">Next: [Creating the tests](creating-the-tests.md)</span>

[PyramidalTests]: https://pyramidal-tests.thenlabs.org
[Composer]: https://getcomposer.org/
[PHPUnit]: https://phpunit.de/