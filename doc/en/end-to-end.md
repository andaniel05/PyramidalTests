# Creating end to end tests.

Sometimes we need to create certain tests that are very expensive in terms of time and/or resources since they involve interaction with other system processes, such as opening a new instance of a web browser, etc.

In order to create this type of tests, there are the functions `setUpBeforeClassOnce()` and `tearDownAfterClassOnce()`.

>Equivalently there are also the `beforeAllOnce()` and `afterAllOnce()` functions.

The example below is an end-to-end test that does is open an instance of [Google Chrome](https://en.wikipedia.org/wiki/Google_Chrome), visit the [Wikipedia ](https://www.wikipedia.com), write "Cuba" in the search engine and press enter. As you go through the different steps, things like page titles, etc. are tested.

>For interaction with the browser, [php-webdriver](https://github.com/php-webdriver/php-webdriver) is being used.

As you can see, both in the outer case and in the inner case, the `setUpBeforeClassOnce()` function is used, which is used to indicate that this code will be executed only once regardless of the number of inner cases that exist.

Put more bluntly, when this test file is executed, a single instance of the browser will be opened and the steps discussed above will be carried out. On the other hand, if only the `setUpBeforeClass` functions were being used, this would imply that two instances of the browser would be opened, since when executing the innermost case, the code of the outermost one would be inherited, as we have seen so far.

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

The result of running the example is shown below. As we discussed earlier, all execution was carried out with a single instance of the browser, despite the fact that the result shows two test cases.

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

<span class="float-start">Back: [Creating the tests](creating-the-tests.md)</span>
<span class="float-end">Next: [Working with decorators](decorators.md)</span>