PHP Database Abstraction Layer
====================

PHP Class to manipulate MySQL databases. 

## Installation

```php
require_once('db.php');
```

## Usage

#### Connect

```php
$db = new Database();
$db->connect('dbname', 'user', 'password');
// Or
Database::connect('dbname', 'user', 'password');
```

List all tables

```php
$tables = $db->getTables();
$tables = Database::getTables();
```

Available methods:

- connect
- getInstance
- getObj
- getDb
- setObj
- disconnect
- setFetchMode
- getFetchMode
- prepare
- query
- exec
- quote
- fetch
- fetchAll
- bind
- lastInsertId
- transaction
- commit
- rollback
- getTables
- getTable
- addColumn
- dropColumn
- emptyTable
- dropTable
- delete
- insert
- replace
- createTable
- update
- createSQL
- limit
- findAll
- findOne



<p>&copy; <a href='http://vadimg.com' target="_blank">Vadim Vincent Gabriel</a> <a href='https://twitter.com/gabrielva' target='_blank'>Follow @gabrielva</a> 2014</p>

