<?php

use Mockery as m;

class DatabaseProcessorTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInsertGetIdProcessing()
    {
        $connection = m::mock(\JTGrimes\LaravelDB2\DB2Connection::class);
        $connection->shouldReceive('insert')->once()->with('sql', ['foo']);
        $connection->shouldReceive('lastInsertID')->once()->andReturn(99);
        $builder = m::mock('Illuminate\Database\Query\Builder');
        $builder->shouldReceive('getConnection')->andReturn($connection);
        $processor = new \JTGrimes\LaravelDB2\DB2Processor();
        $result = $processor->processInsertGetId($builder, 'sql', ['foo'], 'id');
        $this->assertSame(99, $result);
    }
}
