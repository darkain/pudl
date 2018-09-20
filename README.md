# PHP Universal Database Library - PUDL
[![Build Status](https://travis-ci.org/darkain/pudl.svg?branch=master)](https://travis-ci.org/darkain/pudl)



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
Engine | Support
-------|--------
MySQL | *Officially Supported*
PerconaDB | *Officially Supported*
MariaDB | *Officially Supported*
Galera Clustering | *Officially Supported*
NULL | *Officially Supported*
Microsoft SQL | *Experimental Support*
SQLite | *Experimental Support*
ODBC | *Experimental Support*
Pervasive | *Experimental Support*
PostgreSQL | *Experimental Support*
PDO | *Experimental Support*
Shell | *Experimental Hack*
Web | *Experimental Hack*


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
	'type'     => 'MySqli',
	'server'   => 'localhost',
	'database' => 'DatabaseName',
	'username' => 'AwesomeGuy9001',
	'password' => 'SuperDuperSecretSauce',
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


### SELECT
---
These are the basic long-form methods that closest match raw SQL. Most other
methods rely heavily upon these, but can also be called directly from the
application layer.

```php
$result = $db->select($column [,$table=false] [,$clause=false] [,$order=false] [,$limit=false] [,$offset=false]);
// SELECT {$column} [FROM {$table}] [WHERE ($clause)] [ORDER BY {$order}] [LIMIT {$limit}{,$offset}]
// returns a pudlResult instance
```
```php
$result = $db->having($column, $table [,$clause=false] [,$having=false] [,$order=false] [,$limit=false] [,$offset=false]);
// SELECT {$column} FROM {$table} [WHERE ($clause)] [HAVING ($having)] [ORDER BY {$order}] [LIMIT {$limit}{,$offset}]
// returns a pudlResult instance
```
```php
$result = $db->group($column, $table [,$clause=false] [,$group=false] [,$order=false] [,$limit=false] [,$offset=false]);
// SELECT {$column} FROM {$table} [WHERE ($clause)] [GROUP BY ($group)] [ORDER BY {$order}] [LIMIT {$limit}{,$offset}]
// returns a pudlResult instance
```
```php
$result = $db->groupHaving($column, $table [,$clause=false] [,$group=false] [,$having=false] [,$order=false] [,$limit=false] [,$offset=false]);
// SELECT {$column} FROM {$table} [WHERE ($clause)] [GROUP BY ($group)] [HAVING ($having)] [ORDER BY {$order}] [LIMIT {$limit}
// returns a pudlResult instance
```
```php
$result = $db->distinct($column, $table [,$clause=false] [,$order=false] [,$limit=false] [,$offset=false]);
// SELECT DISTINCT {$column} FROM {$table} [WHERE ($clause)] [ORDER BY {$order}] [LIMIT {$limit}{,$offset}]
// returns a pudlResult instance
```
```php
$row = $db->selectRow($column, $table [,$clause=false] [,$order=false], $limit=1 [,$offset=false]);
// SELECT {$column} FROM {$table} [WHERE ($clause)] [ORDER BY {$order}] [LIMIT {$limit}{,$offset}]
// returns an (array) of the given row
```
```php
$row = $db->row($table [,$clause=false] [,$order=false]);
// SELECT * FROM {$table} [WHERE ($clause)] [ORDER BY {$order}] LIMIT 1
// returns an (array) of the given row
```
```php
$row = $db->rowEx($column, $table [,$clause=false] [,$order=false]);
// SELECT {$column} FROM {$table} [WHERE ($clause)] [ORDER BY {$order}] LIMIT 1
// returns an (array) of the given row
```
```php
$row = $db->rowId($table, $column [,$id=false]);
// SELECT * FROM {$table} WHERE ({$column}={$id}) LIMIT 1
// returns an (array) of the given row
```
```php
$rows = $db->selectRows($col, $table [,$clause=false] [,$order=false] [,$limit=false] [,$offset=false]);
// SELECT {$column} FROM {$table} [WHERE ($clause)] [ORDER BY {$order}] [LIMIT {$limit}{,$offset}]
// returns an (array) of multiple row (arrays)
```
```php
$rows = $db->rows($table [,$clause=false] [,$order=false]);
// SELECT * FROM {$table} [WHERE ($clause)] [ORDER BY {$order}]
// returns an (array) of multiple row (arrays)
```
```php
$rows = $db->rowId($table, $column [,$id=false]);
// SELECT * FROM {$table} WHERE ({$column}={$id})
// returns an (array) of multiple row (arrays)
```

There is also the more complex `selex` method which is also used by `pudlOrm`
and other internal features. The `selex` method takes in an associative (array)
with each of the query sections being optional. Parameters that are omitted from
the (array) will not appear in the generated SQL query. Each of the (array) keys
listed below are all optional. The `selex` method returns an instance of
`pudlQuery`.

```php
$result = $db->selex([
	// [SELECT {column}]
	// if omitted or empty becomes [SELECT *]
	'column'	= '',

	// [FROM {table}]
	// if omitted, SQL error may be generated
	'table'		= '',

	// [WHERE (clause)]
	'clause'	= '',

	// [GROUP BY {group}]
	'group'		= '',

	// [HAVING (having)]
	'having'	= '',

	// [ORDER BY (order)]
	'order'		= '',

	// [LIMIT {$limit}{,$offset}]
	'limit'		= '',
	'offset'	= '',
]);
```





### pudlResult
---
The `pudlResult` is the main object instance returned by most `pudl` API calls.
This object supports most common and standard features found in other SQL
drivers, with a few additional features geared specifically for `pudl`. Various
PHP language features are also supported by the `pudlResult` object as
demonstrated below.

```php
// Get rows using variable-function syntax
while ($data = $result()) {
	var_dump($data);
}


// Get rows using foreach syntax
foreach ($result as $data) {
	var_dump($data);
}


// Get rows using object-method syntax
while ($data = $result->row()) {
	var_dump($row);
}


// Get all rows at once
$rows = $result->rows();


// Get all rows as a JSON string
$json = $result->json();
```

Once usage of the result is finished, it is a good idea to call `free()` on the
`pudlObject` instance so that way it no longer holds on to the resource
allocation. If this isn't called manually, resources will be freed at the end
of the current running PHP script automatically. However, if there are many
`pudl` calls without freeing resources, there is a chance of hitting PHP's
defined memory limit. Luckily, there are also shortcut functions to get the row
data and free the `pudlObject` resource within a single call to make this
process more elegant.

```php
// Same as calling $result->rows(); $result->free();
$rows = $result->complete();


// Same as calling $result->complete(), for key/value pairs (2 column SQL result sets)
$rows = $result->collection();


// Same as calling $result->json(); $result->free();
$json = $result->completeJson();


// Can also be used on pudl calls that return a $result
$rows = $db->select('*', 'table')->complete();


// Oh, and of course JSON too!
$json = $db->select('*', 'table')->completeJson();
```

There are a few more helpful methods for `pudlObject` as well.

```php
// Get the query that generated this $result set
$query = $result->query();
```
```php
// Check if there was an error code with this query
$error = $result->error();
```
```php
// Get the number of rows in the $result set
$int = $result->count();
// or
$int = count($result);
```
```php
// Get if there are rows in the $result set
$bool = $result->hasRows();
```
```php
// Move the internal row pointer to the 10th row in the $result set
$result->seek(10);
```
```php
// Move the internal row pointer to the first row in the $result set
$result->rewind();
// or
rewind($result);
```
```php
// Check if the internal row pointer is point to a valid row
$bool = $result->valid();
```
```php
// Get the internal row pointer
$int = $result->key();
// or
$int = key($result);
```
```php
// Get the current row in the $result set without moving the internal row pointer
$row = $result->current();
// or
$row = current($result);
```
```php
// Move the internal row pointer to the next row and return that row
$row = $result->next();
// or
$row = next($result);
// or
$row = $result();
// or
$row = $result->row();
```
```php
// Get the number of column fields in the $result set
$int = $result->fields();
```
```php
// Get information on a particular column field in the $result set
$data = $result->getField($column);
```
```php
// Get information on all column fields in the $result set
$data = $result->listFields();
```
