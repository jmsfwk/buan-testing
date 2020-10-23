<?php

namespace Buan\Testing;

use Buan\Database;

trait DatabaseTransactions
{
    /**
     * Handle database transactions on the specified connections.
     */
    public function beginDatabaseTransaction(): void
    {
        foreach ($this->connectionsToTransact() as $name) {
            Database::getConnection($name)->beginTransaction();
        }

        $this->duringTeardown(function () {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = Database::getConnection($name);

                $connection->rollBack();
            }
        });
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact()
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact : ['default'];
    }
}
