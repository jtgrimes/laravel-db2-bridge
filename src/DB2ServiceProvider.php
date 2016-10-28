<?php namespace JTGrimes\LaravelDB2;

use Illuminate\Support\ServiceProvider;

class DB2ServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['db']->extend('db2', function ($config) {
            return new DB2Connection(
                $config['dbname'],
                $config['username'],
                $config['password'],
                $config['db2_options']
            );
        });
    }
}
