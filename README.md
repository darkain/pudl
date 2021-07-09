# PHP Universal Database Library - PUDL
[![Build Status](https://travis-ci.com/darkain/pudl.svg?branch=master)](https://travis-ci.com/darkain/pudl)


* [Documentation (work in progress)](https://github.com/darkain/pudl-docs/blob/master/README.md)
* [About](#about)
* [Supported Database Engines](#supported-database-engines)
* [License](#license)
* [Usage](#usage)
	* [Getting Started](#getting-started)
	* [INSERT](#insert)
	* [UPDATE](#update)
	* [SELECT](#select)
* [General API Guide](#general-api-guide)
	* [Method Parameters](#method-parameters)
	* [SELECT](#select)
	* [pudlResult](#pudlresult)



## About
The primary function of this library is to provide a common interface for
interacting with several different database engines without worrying about
implementation specific syntax. PUDL takes basic PHP functions and data types,
and then converts these over to engine specific SQL queries automatically.
This is accomplished using a simplistic procedural style of programming rather
than a more complex ORM style of programming. No custom classes outside of PUDL
are required to take full advantage of this library.

The [test](https://github.com/darkain/pudl/tree/master/test) folder contains
a series of examples of PUDL function calls and their resulting SQL query
strings. This is a good place to get an idea of what the PUDL library is
designed for beyond what is documented on this page.



## Supported Database Engines
Engine | Class | Support | Info
-------|-------|---------|-----
[MySQL](https://www.mysql.com) | [pudlMySqli](https://github.com/darkain/pudl/blob/master/mysql/pudlMySqli.php) <br> [pudlMySql](https://github.com/darkain/pudl/blob/master/mysql/pudlMySql.php) | *Officially Supported* | Both modern [php-mysqli](http://php.net/manual/en/book.mysqli.php) and legacy [php-mysql](http://php.net/manual/en/mysql.php) available
[MariaDB](https://mariadb.org) | [pudlMySqli](https://github.com/darkain/pudl/blob/master/mysql/pudlMySqli.php) <br> [pudlMySql](https://github.com/darkain/pudl/blob/master/mysql/pudlMySql.php) | *Officially Supported* | Same as MySQL
[Percona](https://www.percona.com/software/mysql-database/percona-server) | [pudlMySqli](https://github.com/darkain/pudl/blob/master/mysql/pudlMySqli.php) <br> [pudlMySql](https://github.com/darkain/pudl/blob/master/mysql/pudlMySql.php) | *Officially Supported* | Same as MySQL
[Galera Clustering](http://galeracluster.com/products/) | [pudlGalera](https://github.com/darkain/pudl/blob/master/mysql/pudlGalera.php) | *Officially Supported* | Uses [pudlMySqli](https://github.com/darkain/pudl/blob/master/mysql/pudlMySqli.php) with additional cluster features
[NULL](https://en.wikipedia.org/wiki/Null_device) | [pudlNull](https://github.com/darkain/pudl/blob/master/null/pudlNull.php) | *Officially Supported* | Essentially /dev/null the database
[Microsoft SQL](https://www.microsoft.com/en-us/sql-server/) | [pudlSqlSrv](https://github.com/darkain/pudl/blob/master/mssql/pudlSqlSrv.php) <br> [pudlMsSql](https://github.com/darkain/pudl/blob/master/mssql/pudlMsSql.php) | *Experimental Support* | Both modern [php-sqlsrv](http://php.net/manual/en/book.sqlsrv.php) and legacy [php-mssql](http://php.net/manual/en/book.mssql.php) available
[SQLite](https://www.sqlite.org/index.html) | [pudlSqlite](https://github.com/darkain/pudl/blob/master/sqlite/pudlSqlite.php) | *Experimental Support* | Uses the [php-sqlite3](http://php.net/manual/en/book.sqlite3.php) driver
[ODBC](https://en.wikipedia.org/wiki/Open_Database_Connectivity) | [pudlOdbc](https://github.com/darkain/pudl/blob/master/odbc/pudlOdbc.php) | *Experimental Support* | Uses the [php-odbc](http://php.net/manual/en/book.uodbc.php) driver
[Actian PSQL](http://www.pervasive.com/database/Home/Products/PSQLv11.aspx) | [pudlOdbc](https://github.com/darkain/pudl/blob/master/odbc/pudlOdbc.php) | *Experimental Support* | Supported through ODBC
[PostgreSQL](https://www.postgresql.org) | [pudlPgSql](https://github.com/darkain/pudl/blob/master/pgsql/pudlPgSql.php) | *Experimental Support* | Uses the [php-pgsql](http://php.net/manual/en/book.pgsql.php) driver
[PDO](http://php.net/manual/en/book.pdo.php) | [pudlPdo](https://github.com/darkain/pudl/blob/master/pdo/pudlPdo.php) | *Experimental Support* | Uses the [php-pdo](http://php.net/manual/en/book.pdo.php) driver
Shell | [pudlShell](https://github.com/darkain/pudl/blob/master/sql/pudlShell.php) | *Experimental Hack* | Custom JSON proxy interface over shell commands
Web | [pudlWeb](https://github.com/darkain/pudl/blob/master/sql/pudlWeb.php) | *Experimental Hack* | Custom JSON proxy interface over HTTP(s)
Clone | [pudlClone](https://github.com/darkain/pudl/blob/master/clone/pudlClone.php) | *Experimental Hack* | Cloned interface forwarding calls to another PUDL instance


## License
This software library is licensed under the BSD 2-clause license, and may be
freely used in any project (commercial, freelance, hobby, or otherwise) which
is compatible with this license. See
[LICENSE](https://github.com/darkain/pudl/blob/master/LICENSE)
for more details.


## Usage




### Getting Started
---
First, create an instance of PUDL for your specific database type
```php
require_once('pudl/pudl.php');

$db = pudl::instance([
	'type'		=> 'mariadb',
	'server'	=> 'localhost',
	'database'	=> 'DatabaseName',
	'username'	=> 'AwesomeGuy9001',
	'password'	=> 'SuperDuperSecretSauce',
]);
```




### INSERT
---
Let's start by showing the most intuitive conversion from PHP to SQL.

```php
$db->insert('movies', [
	'id'		=> 1,
	'title'		=> 'Star Wars',
	'subtitle'	=> 'The Force Awakens',
	'director'	=> 'J.J. Abrams',
	'runtime'	=> 136,
]);
```

This will result in the following query being generated and executed:

```sql
INSERT INTO `movies` (`id`, `title`, `subtitle`, `director`, `runtime`) VALUES (1, 'Star Wars', 'The Force Awakens', 'J.J. Abrams', 136)
```

Inserting data into the database uses a normal and intuitive PHP associative
array as a key-value pair. PUDL separates out theses *keys* and *values*
automatically to form the *column* and *value* pair to **INSERT** into the
database.




### UPDATE
---
**UPDATE** queries are a similar syntax. Let's say we need to update the title
because we initially put it in wrong. You can use the following to do so.

```php
$db->update('movies', [
	'title'	=> 'Star Wars: Episode VII',
], [
	'id'	=> 1,
]);
```

Resulting SQL:
```sql
UPDATE `movies` SET `title`='Star Wars: Episode VII' WHERE (`id`=1)
```

With this, we use the same *key* and *value* pair with **UPDATE** as we do with
**INSERT**. Additionally, we also use the same *key* and *value* pair to
generate our **WHERE** clause.




### SELECT
---
As with the **UPDATE** query, anything in PUDL that takes a **WHERE** clause
can take a *key* and *value* pair. Here are some examples of **SELECT**
statements.

**PHP**:
```php
$data = $db->rows('movies');
var_export($data);
```
**Generated SQL**:
```sql
SELECT * FROM `movies`
```
**Output**:
```
array (
	0 =>
	array (
		'id' => 1,
		'title' => 'Star Wars: Episode VII',
		'subtitle' => 'The Force Awakens',
		'director' => 'J.J. Abrams',
		'runtime' => 136,
	),
)
```


We only have 1 item in the **`movies`** table right now, so only one row is
returned. The **rows()** function returns all rows that match a particular
**WHERE** clause. In this example above, the optional **WHERE** clause is not
specified. Here is an example with it:

**PHP**:
```php
$data = $db->rows('movies', ['director'=>'J.J. Abrams']);
var_export($data);
```
**Generated SQL**:
```sql
SELECT * FROM `movies` WHERE (`director`='J.J. Abrams')
```
**Output**:
```
array (
	0 =>
	array (
		'id' => 1,
		'title' => 'Star Wars: Episode VII',
		'subtitle' => 'The Force Awakens',
		'director' => 'J.J. Abrams',
		'runtime' => 136,
	),
)
```


If we only want to get a single row from the database, we can use **row()**
instead of **rows()**. This will return a single dimensional array instead
of a two-dimensional array. This function also forces a **LIMIT** of **1**.

**PHP**:
```php
$data = $db->row('movies', ['director'=>'J.J. Abrams']);
var_export($data);
```
**Generated SQL**:
```sql
SELECT * FROM `movies` WHERE (`director`='J.J. Abrams') LIMIT 1
```
**Output**:
```
array (
	'id' => 1,
	'title' => 'Star Wars: Episode VII',
	'subtitle' => 'The Force Awakens',
	'director' => 'J.J. Abrams',
	'runtime' => 136,
)
```


Often times you'll need more than one item in your **WHERE** clause. This is
easily done with the automatic **AND** clauses.

**PHP**:
```php
$data = $db->row('movies', [
	'director'	=> 'J.J. Abrams',
	'subtitle'	=> 'The Force Awakens',
]);
```
**Generated SQL**:
```sql
SELECT * FROM `movies` WHERE (`director`='J.J. Abrams' AND `subtitle`='The Force Awakens') LIMIT 1
```


Nesting an array inside of another array creates an **OR** clause

**PHP**:
```php
$data = $db->row('movies', [
	'director'		=> 'J.J. Abrams',
	[
		['title'	=> 'Star Wars'],
		['title'	=> 'Star Wars: Episode VII'],
		['title'	=> 'Episode VII'],
	]
]);
```
**Generated SQL**:
```sql
SELECT * FROM `movies` WHERE (`director`='J.J. Abrams' AND ((`title`='Star Wars') OR (`title`='Star Wars: Episode VII') OR (`title`='Episode VII'))) LIMIT 1
```




## General API Guide


### Method Parameters
---
There are a few method parameter variable `$names` that reoccur frequently within
PUDL. Except for a few cases specifically noted, wherever you see these `$names`,
their values are of MIXED data type, each doing something different depending on
the data type passed into the method. This can be thought of similarly to
overloaded methods in C++, but with a significantly more dynamic nature. Listed
here are the most common parameter names and what each data type represents.


#### $value

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/parameters/value.md



#### $columns

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/parameters/columns.md



#### $tables

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/parameters/tables.md




#### $clause

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/parameters/clause.md



#### $having

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/parameters/having.md



#### $on

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/parameters/on.md




### SELECT
---

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/pudl/select.md




### pudlResult
---

This section has been migrated to:
https://github.com/darkain/pudl-docs/blob/master/pudl/pudlResult.md
