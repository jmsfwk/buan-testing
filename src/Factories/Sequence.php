<?php

namespace Buan\Testing\Factories;

use Closure;

class Sequence
{
    /**
     * The sequence of return values.
     *
     * @var array
     */
    protected $sequence;

    /**
     * The count of the sequence items.
     *
     * @var int
     */
    protected $count;

    /**
     * The current index of the sequence.
     *
     * @var int
     */
    protected $index = 0;

    /**
     * Create a new sequence instance.
     *
     * @param  array  $sequence
     * @return void
     */
    public function __construct(...$sequence)
    {
        $this->sequence = $sequence;
        $this->count = count($sequence);
    }

    /**
     * Get the next value in the sequence.
     *
     * @return mixed
     */
    public function __invoke()
    {
        if ($this->index > ($this->count - 1)) {
            $this->index = 0;
        }

        return self::tap($this->sequence[$this->index], function () {
            ++$this->index;
        });
    }

    private static function tap($value, Closure $callback)
    {
        $callback($value);

        return $value;
    }
}
