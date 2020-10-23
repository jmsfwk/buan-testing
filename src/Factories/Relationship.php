<?php

namespace Buan\Testing\Factories;

use Buan\Model;

class Relationship
{
    /**
     * The related factory instance.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * Create a new child relationship instance.
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create the child relationship for the given parent model.
     */
    public function createFor(Model $parent): void
    {
        $parent->addRelatives($this->factory->state([
            $parent->getForeignKey($this->factory->newModel()) => $parent->getPrimaryKeyValue(),
        ])->create());
    }
}
