<?php namespace JTGrimes\LaravelDB2;

use Illuminate\Database\Connection;

class DB2Connection extends Connection
{
    private $connection;

    private $queryBuilder;

    public function __construct($database, $username, $password, $options = [])
    {
        $this->setQueryGrammar(new DB2Grammar);
        $this->setPostProcessor(new DB2Processor);
        $this->connection = $this->db2connect($database, $username, $password, $options);
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function select($query, $bindings = [])
    {
        if ($this->pretending()) {
            return [];
        }

        $request = $this->db2prepare($this->connection, $query);
        $this->db2execute($request, $bindings);
        $results = [];
        while ($row = $this->db2fetchassoc($request)) {
            $record = [];
            foreach ($row as $field => $value) {
                //TODO: make this a config setting
                //DB2 upper cases all column names
                $propertyName = camel_case(strtolower($field));
                //TODO: make this a config setting
                // DB2 also pads results with spaces
                $record[$propertyName] = trim($value);
            }
            $results[] = $record;
        }
        return $results;
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string $query
     * @param  array $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        if ($this->pretending()) {
            return true;
        }
        $request = $this->db2prepare($this->connection, $query);
        return $this->db2execute($request, $bindings);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string $query
     * @param  array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        if ($this->pretending()) {
            return true;
        }
        $request = $this->db2prepare($this->connection, $query);
        $this->db2execute($request, $bindings);
        return $this->db2numrows($request);
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string $query
     * @return bool
     * @throws DB2Exception
     */
    public function unprepared($query)
    {
        // let's not do this:
        throw new DB2Exception('Unprepared queries not permitted');
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->db2autocommit($this->connection, false);
        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->db2commit($this->connection);
        // and turn autocommit back on...
        $this->db2autocommit($this->connection, true);
        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->db2rollback($this->connection);
        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        // we don't support this...
        return 0;
    }

    public function pretending()
    {
        return $this->pretending;
    }

    public function query()
    {
        $this->queryBuilder = new DB2QueryBuilder($this);
        return $this->queryBuilder;
    }

    public function lastInsertID()
    {
        return db2_last_insert_id($this->connection);
    }

    protected function db2connect($databse, $username, $password, $options = [])
    {
        $result = db2_connect($databse, $username, $password, $options);
        if (!$result) {
            throw new DB2Exception(db2_conn_errormsg());
        }
        return $result;
    }

    protected function db2prepare($connection, $query)
    {
        $result = db2_prepare($connection, $query);
        if (!$result) {
            throw new DB2Exception(db2_stmt_errormsg());
        }
        return $result;
    }

    protected function db2execute($statement, $parameters)
    {
        $result = db2_execute($statement, $parameters);
        // TODO: add query logging here
        if (!$result) {
            throw new DB2Exception(db2_stmt_errormsg());
        }
        return $result;
    }

    protected function db2fetchassoc($statement, $row_number = null)
    {
        if (is_null($row_number)) {
            return db2_fetch_assoc($statement);
        }
        return db2_fetch_assoc($statement, $row_number);
    }

    protected function db2numrows($statement)
    {
        return db2_num_rows($statement);
    }

    protected function db2autocommit($connection, $value = null)
    {
        if (is_null($value)) {
            // this is a getter...
            return db2_autocommit($connection);
        }
        return db2_autocommit($connection, $value);
    }

    protected function db2commit($connection)
    {
        return db2_commit($connection);
    }

    protected function db2rollback($connection)
    {
        return db2_rollback($connection);
    }
}
