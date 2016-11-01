<?php namespace JTGrimes\LaravelDB2;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;

class DB2Processor extends Processor
{
    /**
     * Process an  "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->lastInsertID();

        return is_numeric($id) ? (int) $id : $id;
    }
}
