<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model;

use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
abstract class AbstractModel
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var Closure
     */
    protected $closure;

    public function __construct(string $title, Closure $closure)
    {
        $this->closure = $closure;
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getClosure(): Closure
    {
        return $this->closure;
    }
}
