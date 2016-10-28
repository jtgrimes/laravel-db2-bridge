<?php namespace JTGrimes\LaravelDB2;

use Illuminate\Database\Query\Builder;

class DB2QueryBuilder extends Builder
{

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public function exists()
    {
        $sql = $this->grammar->compileSelect($this);

        $results = $this->connection->select($sql, $this->getBindings());

        return isset($results[0]);
    }
}
