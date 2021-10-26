<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Utils;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Proxy
{
    protected $subjects = [];

    public function __construct(array $subjects)
    {
        $this->subjects = $subjects;
    }

    public function __call($methodName, $arguments)
    {
        foreach ($this->subjects as $subject) {
            $callable = [$subject, $methodName];

            if (is_callable($callable)) {
                return call_user_func_array($callable, $arguments);
            }
        }
    }
}
