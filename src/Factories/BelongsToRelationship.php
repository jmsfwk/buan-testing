<?php

namespace Buan\Testing\Factories;

use Buan\Inflector;
use Buan\Model;
use Closure;

class BelongsToRelationship
{
    /**
     * The related factory instance.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relationship;

    /**
     * The cached, resolved parent instance ID.
     *
     * @var mixed
     */
    protected $resolved;

    /**
     * Create a new "belongs to" relationship definition.
     */
    public function __construct(Factory $factory, string $relationship = null)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Get the parent model attributes and resolvers for the given child model.
     */
    public function attributesFor(Model $model): array
    {
        return [
            $this->relationship ?: $this->guessForeignKeyName() => $this->resolver(),
        ];
    }

    /**
     * Get the deferred resolver for this relationship's parent ID.
     *
     * @return Closure
     */
    protected function resolver()
    {
        return function () {
            if (!$this->resolved) {
                return $this->resolved = $this->factory->create()->getPrimaryKeyValue();
            }

            return $this->resolved;
        };
    }

    protected function guessForeignKeyName(): string
    {
        $modelName = $this->factory->newModel()->modelName;

        return Inflector::upperCamelCaps_lowerUnderscored($modelName).'_id';
    }
}
