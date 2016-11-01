# laravel-db2-bridge
Use Laravel's database and Eloquent tools on DB2 with no fuss

## :WARNING: Heads Up :WARNING:
This is a very very preliminary release of this package and should be used at your own risk. The code here 
does everything I need it to, but does not yet **fully** implement Laravel's database or Eloquent components.
Suggestions are gratefully accepted. Pull Requests are even more gratefully accepted.

License
==========

LaravelDB2Bridge is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT), the same license Laravel uses.

Requirements
============

LaravelDB2Bridge wraps PHP's native db2_* database functions to be called from Laravel.  You must have 
the [`ibm_db2` extension](https://secure.php.net/manual/en/book.ibm-db2.php) installed to use this package.

Installation
============

Via Composer

````
$ composer require jtgrimes\laravel-db2-bridge
````

Once Composer has installed or updated your packages, you need to register
LaravelDB2Bridge with Laravel itself. Open up `/config/app.php` and
find the providers key towards the bottom and add:

````
'JTGrimes\LaravelDB2\DB2ServiceProvider'
````

Configuration
=============

When you use LaravelDB2Bridge, DB2 becomes just another database driver in Laravel. To configure
your database, open `/config/database.php` and add the following to the `connections` array:

         'db2' => [
             'username'   => env('DB_USERNAME'),
             'password' => env('DB_PASSWORD'),
             'dbname'   => '*LOCAL',
             'options' => [
                 'i5_libl' => 'QTEMP OTHERLIB ANOTHEROTHERLIB',
                 'i5_naming' => 'DB2_I5_NAMING_OFF',
                 ...
              ],
         ],
Obviously, you can tweak those settings to suit your needs. All of the 
[connection options](https://secure.php.net/manual/en/function.db2-connect.php) are available and
can be set in the `options` array.

Usage
=====

Use Laravel's  [database](https://laravel.com/docs/5.3/database) and [Eloquent](https://laravel.com/docs/5.3/eloquent)
packages as you usually would.

Credits
=======
[Taylor Otwell](https://twitter.com/taylorotwell) wrote  [Laravel](http://laravel.com/) which is awesome, but he shares no blame for this package.
[Alan Seiden](https://twitter.com/alanseiden) deserves a little blame for encouraging me to release this package. If you're
running PHP on the IBM iSeries, he's *the* guy to hit up for help.

