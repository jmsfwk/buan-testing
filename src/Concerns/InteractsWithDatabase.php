<?php

namespace Buan\Testing\Concerns;

use Buan\Database;
use Buan\Model;
use Buan\Testing\Constraints\HasInDatabase;
use Buan\Testing\Str;
use PDO;
use PHPUnit\Framework\Constraint\LogicalNot as ReverseConstraint;

trait InteractsWithDatabase
{
    /**
     * Assert that a given where condition exists in the database.
     *
     * @param string|Model $table
     * @param array $data
     * @param string|null $connection
     */
    protected function assertModelExists($model, array $data, $connection = null): self
    {
        if (is_string($model) && Str::endsWith(Str::classBasename($model), 'Model')) {
            $model = new $model;
        }
        if (is_string($model)) {
            $model = Model::create($model);
        }

        $this->assertDatabaseHas($model->getDbTableName(), $data, $connection ?? $model->getDbConnectionName());

        return $this;
    }

    /**
     * Assert that a given where condition exists in the database.
     *
     * @param string $table
     * @param array $data
     * @param string|null $connection
     * @return $this
     */
    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        $this->assertThat(
            $table, new HasInDatabase($this->getConnection($connection), $data)
        );

        return $this;
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param string $table
     * @param array $data
     * @param string|null $connection
     * @return $this
     */
    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        $constraint = new ReverseConstraint(
            new HasInDatabase($this->getConnection($connection), $data)
        );

        $this->assertThat($table, $constraint);

        return $this;
    }

    /**
     * Assert the given record has been deleted.
     *
     * @param \Illuminate\Database\Eloquent\Model|string $table
     * @param array $data
     * @param string|null $connection
     * @return $this
     */
    protected function assertDeleted($table, array $data = [], $connection = null)
    {
        if ($table instanceof Model) {
            return $this->assertDatabaseMissing($table->getDbTableName(),
                [$table->getPrimaryKey() => $table->getPrimaryKeyValue()], $table->getDbConnectionName());
        }

        $this->assertDatabaseMissing($table, $data, $connection);

        return $this;
    }

    /**
     * Get the database connection.
     */
    protected function getConnection(string $connection = null): PDO
    {
        return Database::getConnection($connection ?? 'default');
    }
}
