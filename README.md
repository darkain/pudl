# PHP Universal Database Library - PUDL



## About
The primary function of this library is to provide a common interface for
interacting with several different database engines without worrying about
implementation specific syntax. PUDL takes basic PHP functions and data types,
and then converts these over to engine specific SQL queries automatically.

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
Microsoft SQL | *Experimental Support*
SQLite | *Experimental Support*
ODBC | *Experimental Support*
Pervasive | *Experimental Support*
PostgreSQL | *Experimental Support*
Shell | *Experimental Hack*
Web | *Experimental Hack*


## License
This software library is licensed under the BSD 3-clause license, and may be
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
**UPDATE** queries you a similar syntax. Let's say we need to update the title
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
