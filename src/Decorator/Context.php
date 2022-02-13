<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Decorator;

use Exception;
use Throwable;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
class Context
{
    /**
     * @var object
     */
    protected $subject;

    /**
     * @var object|null
     */
    protected $parent;

    public function __construct(object $subject, ?object $parent = null)
    {
        $this->subject = $subject;
        $this->parent = $parent;
    }

    public function end(): ?object
    {
        return $this->parent;
    }

    public function __call($name, $arguments)
    {
        try {
            return call_user_func_array([$this->subject, $name], $arguments);
        } catch (Throwable $exception) {
            $parent = $this->parent;

            while ($parent) {
                try {
                    return call_user_func_array([$parent, $name], $arguments);
                } catch (Throwable $exception) {
                    $parent = $parent->end();
                }
            }
        }
    }
}