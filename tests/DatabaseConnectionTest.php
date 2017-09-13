<?php

use JTGrimes\LaravelDB2\DB2Connection;
use Mockery as m;

class DatabaseConnectionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSettingDefaultCallsGetDefaultGrammar()
    {
        $connection = $this->getMockConnection();
        $mock = m::mock('StdClass');
        $connection->expects($this->once())->method('getDefaultQueryGrammar')->will($this->returnValue($mock));
        $connection->useDefaultQueryGrammar();
        $this->assertEquals($mock, $connection->getQueryGrammar());
    }

    public function testSettingDefaultCallsGetDefaultPostProcessor()
    {
        $connection = $this->getMockConnection();
        $mock = m::mock('StdClass');
        $connection->expects($this->once())->method('getDefaultPostProcessor')->will($this->returnValue($mock));
        $connection->useDefaultPostProcessor();
        $this->assertEquals($mock, $connection->getPostProcessor());
    }

    public function testSelectOneCallsSelectAndReturnsSingleResult()
    {
        $connection = $this->getMockConnection(['select']);
        $connection->expects($this->once())->method('select')->with('foo', ['bar' => 'baz'])->will($this->returnValue(['foo']));
        $this->assertEquals('foo', $connection->selectOne('foo', ['bar' => 'baz']));
    }

    public function testSelectPropertyCallsDB2()
    {
        $connection = $this->getMockConnection(['db2prepare', 'db2execute', 'db2fetchassoc']);
        $connection->expects($this->once())->method('db2prepare')->will($this->returnValue(new StdClass));
        $connection->expects($this->once())->method('db2execute')->will($this->returnValue(new StdClass));
        $connection->expects($this->at(1))->method('db2fetchassoc')->will($this->returnValue('return'));
        $connection->expects($this->at(2))->method('db2fetchassoc')->will($this->returnValue(null));

        $connection->select('select * from table where x = ?', ['thingy' => 'stuff']);
    }

    public function testInsertCallsTheStatementMethod()
    {
        $connection = $this->getMockConnection(['statement']);
        $connection->expects($this->once())->method('statement')->with($this->equalTo('foo'), $this->equalTo(['bar']))->will($this->returnValue('baz'));
        $results = $connection->insert('foo', ['bar']);
        $this->assertEquals('baz', $results);
    }

    public function testUpdateCallsTheAffectingStatementMethod()
    {
        $connection = $this->getMockConnection(['affectingStatement']);
        $connection->expects($this->once())->method('affectingStatement')->with($this->equalTo('foo'), $this->equalTo(['bar']))->will($this->returnValue('baz'));
        $results = $connection->update('foo', ['bar']);
        $this->assertEquals('baz', $results);
    }

    public function testDeleteCallsTheAffectingStatementMethod()
    {
        $connection = $this->getMockConnection(['affectingStatement']);
        $connection->expects($this->once())->method('affectingStatement')->with($this->equalTo('foo'), $this->equalTo(['bar']))->will($this->returnValue('baz'));
        $results = $connection->delete('foo', ['bar']);
        $this->assertEquals('baz', $results);
    }

    public function testStatementProperlyCallsDB2()
    {
        $connection = $this->getMockConnection(['db2prepare', 'db2execute']);
        $connection->expects($this->once())->method('db2prepare')->will($this->returnValue(new StdClass));
        $connection->expects($this->once())->method('db2execute')->will($this->returnValue(new StdClass));

        $connection->insert('x', ['thingy' => 'stuff']);
    }

    public function testAffectingStatementProperlyCallsDB2()
    {
        $connection = $this->getMockConnection(['db2prepare', 'db2execute', 'db2numrows']);
        $connection->expects($this->once())->method('db2prepare')->will($this->returnValue(new StdClass));
        $connection->expects($this->once())->method('db2execute')->will($this->returnValue(new StdClass));
        $connection->expects($this->once())->method('db2numrows')->will($this->returnValue(5));

        $rows = $connection->update('x', ['thingy' => 'stuff']);
        $this->assertEquals(5, $rows);
    }

    public function testBeganTransactionFiresEventsIfSet()
    {
        $connection = $this->getMockConnection(['getName', 'db2autocommit']);
        $connection->expects($this->any())->method('getName')->will($this->returnValue('name'));
        $connection->expects($this->once())->method('db2autocommit');
        $connection->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('fire')->once()->with(m::type('Illuminate\Database\Events\TransactionBeginning'));
        $connection->beginTransaction();
    }

    public function testCommittedFiresEventsIfSet()
    {
        $connection = $this->getMockConnection(['getName', 'db2autocommit', 'db2commit']);
        $connection->expects($this->any())->method('getName')->will($this->returnValue('name'));
        $connection->expects($this->once())->method('db2autocommit');
        $connection->expects($this->once())->method('db2commit');
        $connection->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('fire')->once()->with(m::type('Illuminate\Database\Events\TransactionCommitted'));
        $connection->commit();
    }

    public function testRollBackedFiresEventsIfSet()
    {
        $connection = $this->getMockConnection(['getName', 'db2rollback']);
        $connection->expects($this->any())->method('getName')->will($this->returnValue('name'));
        $connection->expects($this->once())->method('db2rollback');
        $connection->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('fire')->once()->with(m::type('Illuminate\Database\Events\TransactionRolledBack'));
        $connection->rollBack();
    }

//    public function testFromCreatesNewQueryBuilder()
//    {
//        $conn = new DB2Connection('', '', '');
//        $builder = $conn->table('users');
//        $this->assertInstanceOf('Illuminate\Database\Query\Builder', $builder);
//        $this->assertEquals('users', $builder->from);
//    }

    public function testCanUseCustomQueryBuilder()
    {
        $conn = $this->getMockConnection();
        $conn->setQueryBuilderClass(FakeQueryBuilder::class);
        $conn->setQueryGrammar(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $conn->setPostProcessor(m::mock('Illuminate\Database\Query\Processors\Processor'));
        $builder = $conn->table('users');
        $this->assertInstanceOf(FakeQueryBuilder::class, $builder);
        $this->assertEquals('users', $builder->from);
    }

    public function testPrepareBindings()
    {
        $date = m::mock('DateTime');
        $date->shouldReceive('format')->once()->with('foo')->andReturn('bar');
        $bindings = ['test' => $date];
        $conn = $this->getMockConnection();
        $grammar = m::mock('Illuminate\Database\Query\Grammars\Grammar');
        $grammar->shouldReceive('getDateFormat')->once()->andReturn('foo');
        $conn->setQueryGrammar($grammar);
        $result = $conn->prepareBindings($bindings);
        $this->assertEquals(['test' => 'bar'], $result);
    }

    public function testLogQueryFiresEventsIfSet()
    {
        $connection = $this->getMockConnection();
        $connection->logQuery('foo', [], time());
        $connection->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('fire')->once()->with(m::type('Illuminate\Database\Events\QueryExecuted'));
        $connection->logQuery('foo', [], null);
    }

//    public function testPretendOnlyLogsQueries()
//    {
//        $connection = $this->getMockConnection();
//        $queries = $connection->pretend(function ($connection) {
//            $connection->select('foo bar', ['baz']);
//        });
//        $this->assertEquals('foo bar', $queries[0]['query']);
//        $this->assertEquals(['baz'], $queries[0]['bindings']);
//    }

    public function testSchemaBuilderCanBeCreated()
    {
        $connection = $this->getMockConnection();
        $schema = $connection->getSchemaBuilder();
        $this->assertInstanceOf('Illuminate\Database\Schema\Builder', $schema);
        $this->assertSame($connection, $schema->getConnection());
    }

//    public function testAlternateFetchModes()
//    {
//        $stmt = $this->createMock('PDOStatement');
//        $stmt->expects($this->exactly(3))->method('fetchAll')->withConsecutive(
//            [PDO::FETCH_ASSOC],
//            [PDO::FETCH_COLUMN, 3, []],
//            [PDO::FETCH_CLASS, 'ClassName', [1, 2, 3]]
//        );
//        $pdo = $this->createMock('DatabaseConnectionTestMockPDO');
//        $pdo->expects($this->any())->method('prepare')->will($this->returnValue($stmt));
//        $connection = $this->getMockConnection([], $pdo);
//        $connection->setFetchMode(PDO::FETCH_ASSOC);
//        $connection->select('SELECT * FROM foo');
//        $connection->setFetchMode(PDO::FETCH_COLUMN, 3);
//        $connection->select('SELECT * FROM foo');
//        $connection->setFetchMode(PDO::FETCH_CLASS, 'ClassName', [1, 2, 3]);
//        $connection->select('SELECT * FROM foo');
//    }

    protected function getMockConnection($methods = [], $pdo = null)
    {
        $defaults = ['getDefaultQueryGrammar', 'getDefaultPostProcessor', 'getDefaultSchemaGrammar'];
        $connection = $this->getMockBuilder(DB2Connection::class)
            ->setMethods(array_merge($defaults, $methods))
            ->disableOriginalConstructor()
            ->getMock();
        $connection->queryBuilderClass = \JTGrimes\LaravelDB2\DB2QueryBuilder::class;
        $connection->connection = Mockery::mock('strClass');
        $connection->enableQueryLog();

        return $connection;
    }
}

class FakeQueryBuilder extends \JTGrimes\LaravelDB2\DB2QueryBuilder {}
