<?php

namespace Buan\Testing\Constraints;

use Buan\ModelCriteria;
use PDO;
use PHPUnit\Framework\Constraint\Constraint;

class HasInDatabase extends Constraint
{
    /**
     * Number of records that will be shown in the console in case of failure.
     *
     * @var int
     */
    protected $show = 3;

    /**
     * The database connection.
     *
     * @var PDO
     */
    protected $database;

    /**
     * The data that will be used to narrow the search in the database table.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new constraint instance.
     */
    public function __construct(PDO $database, array $data)
    {
        $this->data = $data;

        $this->database = $database;
    }

    /**
     * Check if the data is found in the given table.
     *
     * @param  string  $table
     * @return bool
     */
    public function matches($table): bool
    {
        $criteria = new ModelCriteria();
        foreach ($this->data as $column => $value) {
            $criteria->addClause(ModelCriteria::EQUALS, $column, $value);
        }
        ['query' => $conditions, 'bindings' => $bindings] = (array)$criteria->sql();

        $stmt = $this->database->prepare("SELECT COUNT(*) AS count FROM `{$table}` {$conditions}");
        foreach ($bindings as $binding) {
            $stmt->bindValue($binding->parameter, $binding->value, $binding->dataType);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['count'] > 0;
    }

    /**
     * Get the description of the failure.
     *
     * @param  string  $table
     * @return string
     */
    public function failureDescription($table): string
    {
        return sprintf(
            "a row in the table [%s] matches the attributes %s.\n\n%s",
            $table, $this->toString(JSON_PRETTY_PRINT), $this->getAdditionalInfo($table)
        );
    }

    /**
     * Get additional info about the records found in the database table.
     *
     * @param  string  $table
     * @return string
     */
    protected function getAdditionalInfo($table)
    {
        $value = reset($this->data);
        $key = key($this->data);
        [$criteria, $bindings] = $this->query([$key => $value]);
        $stmt = $this->database->prepare("SELECT * FROM `{$table}` {$criteria} LIMIT {$this->show}");
        $this->bind($stmt, $bindings)->execute();
        $similarResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($similarResults) {
            $description = 'Found similar results: '.json_encode($similarResults, JSON_PRETTY_PRINT);
        } else {
            $results = $this->database->query("SELECT * FROM `{$table}` LIMIT {$this->show}", PDO::FETCH_ASSOC)
                ->fetchAll();

            if (!$results) {
                return 'The table is empty.';
            }

            $description = 'Found: '.json_encode($results, JSON_PRETTY_PRINT);
        }

        ['count' => $count] = $this->database->query("SELECT COUNT(*) as count FROM `{$table}` LIMIT {$this->show}", PDO::FETCH_ASSOC)
            ->fetch();
        if ($count > $this->show) {
            $description .= sprintf(' and %s others', $count - $this->show);
        }

        return $description;
    }

    /**
     * Get a string representation of the object.
     *
     * @param  int  $options
     * @return string
     */
    public function toString($options = 0): string
    {
        return json_encode($this->data, $options);
    }

    private function query(array $data): array
    {
        $criteria = new ModelCriteria();
        foreach ($data as $column => $value) {
            $criteria->addClause(ModelCriteria::EQUALS, $column, $value);
        }
        ['query' => $query, 'bindings' => $bindings] = (array)$criteria->sql();

        return [$query, $bindings];
    }

    private function bind(\PDOStatement $statement, array $bindings): \PDOStatement
    {
        foreach ($bindings as $binding) {
            $statement->bindValue($binding->parameter, $binding->value, $binding->dataType);
        }

        return $statement;
    }
}
