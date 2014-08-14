<?php

require_once('db.php');

$db = new Database();
$db->connect('dbname', 'user', 'password');
// List all tables
$tables = $db->getTables();

// Or
Database::connect('dbname', 'user', 'password');
$tables = Database::getTables();

// Available Methods
/*
connect
getInstance
getObj
getDb
setObj
disconnect
setFetchMode
getFetchMode
prepare
query
exec
quote
fetch
fetchAll
bind
lastInsertId
transaction
commit
rollback
getTables
getTable
addColumn
dropColumn
emptyTable
dropTable
delete
insert
replace
createTable
update
createSQL
limit
findAll
findOne
*/