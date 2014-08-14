<?php

class Database {
	/**
	 * Singleton object
	 * @var object
	 */
	protected static $instance = null;
	/**
	 * Specific driver object
	 * @var object
	 */
	protected $obj = null;
	/**
	 * PDO Prepare statement
	 */
	protected $prepare;
	/**
	 * PDO object
	 * @var object
	 */
	protected $handle = null;
	/**
	 * Connect to database
	 *
	 */
	public static function connect($dbname, $dbuser, $dbpassword, $dbhost='localhost', $dbextra=null) {
		try {
			$dsn = 'mysql:host=' . $dbhost . ';dbname=' . $dbname . $dbextra;
			self::getInstance()->handle = new PDO($dsn, $dbuser, $dbpassword);
			/* turn on exception throwing */
			self::getInstance()->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $e) {
			throw new Exception('Could not connect to the database: ' . $e->getMessage());
		}
	}
	
	/**
	 * Singletone instance for this class
	 * @return object
	 */
	public static function getInstance() {
		if(self::$instance !== null) {
			return self::$instance;
		}
		self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Return DB Specific object
	 *
	 */
	public static function getObj() {
		return self::getInstance()->obj;
	}
	/**
	 * Return PDO Handler
	 *
	 */
	public static function getDb() {
		return self::getInstance()->handle;
	}
	/**
	 * Set DB Specific object
	 *
	 */
	public static function setObj($obj) {
		self::getInstance()->obj = $obj;
	}
	/**
	 * Disconnect from database kill PDO Handler
	 *
	 */
	public static function disconnect() {
		self::getInstance()->handle = null;
	}
	/**
	 * Set the PDO Fetch Mode
	 *
	 */
	public static function setFetchMode($mode) {
		self::getInstance()->fetchMode = $mode;
		return self::getInstance();
	}
	/**
	 * Return the current PDO fetch mode
	 *
	 */
	public static function getFetchMode() {
		return self::getInstance()->fetchMode;
	}
	/**
	 * Prepare statement
	 *
	 */
	public static function prepare($sql) {
		return self::getInstance()->handle->prepare($sql);
	}
	/**
	 * Query sql statement
	 *
	 */
	public static function query($sql, $values=array()) {
		self::getInstance()->prepare = self::getInstance()->prepare($sql);
		self::getInstance()->prepare->execute($values);
		return self::getInstance()->prepare;
	}
	/**
	 * Execute sql statement
	 *
	 */
	public static function exec($sql, $values=array()) {
		if(count($values)) {
			self::getInstance()->prepare = self::getInstance()->prepare($sql);
			return self::getInstance()->prepare->execute($values);
		} else {
			return self::getInstance()->handle->exec($sql);
		}
	}

	/**
	 * Quote value
	 *
	 */
	public static function quote($v) {
		return self::getInstance()->handle->quote($v);
	}
	/**
	 * Fetch values from PDO statement
	 *
	 */
	public static function fetch($mode=null) {
		$_mode = ($mode!== null ? $mode : PDO::FETCH_OBJ);
		return self::getInstance()->prepare->fetch($_mode);
	}
	/**
	 * Fetch all values from PDO Statement
	 *
	 */
	public static function fetchAll($mode=null) {
		$_mode = ($mode!== null ? $mode : PDO::FETCH_OBJ);
		return self::getInstance()->prepare->fetchAll($_mode);
	}
	/**
	 * Bind value to a pdo statement
	 *
	 */
	public static function bind($key, $value, $type=PDO::PARAM_STR) {
		return self::getInstance()->prepare->bindValue($key, $value, $type);
	}
	/**
	 * Get last insert ID
	 *
	 */
	public static function lastInsertId($name=null) {
		return self::getInstance()->handle->lastInsertId($name);
	}
	/**
	 * being transaction
	 *
	 */
	public static function transaction() {
		return self::getInstance()->handle->beginTransaction();
	}
	/**
	 * commit transaction
	 *
	 */
	public static function commit() {
		return self::getInstance()->handle->commit();
	}
	/**
	 * rollback transaction
	 *
	 */	
	public static function rollback() {
		return self::getInstance()->handle->rollBack();
	}
	/**
	 * class destructor
	 * @see disconnect
	 *
	 */
	public function __destruct() {
		self::getInstance()->disconnect();
	}
	
	/**
	 * Return a list of tables within the selected database
	 * @return array
	 */
	public static function getTables() {
		$rows = self::query("SHOW TABLES;")->fetchAll(PDO::FETCH_COLUMN, 0);
		$tables = array();
		foreach($rows as $table) {
			$tables[$table] = $table;
		}

		return $tables;
	}
	/**
	 * Check if table exists in the database
	 * @param string $name
	 * @return boolean
	 */
	public static function getTable($name) {
		$tables = self::getTables();
		return isset($tables[$name]) ? true : false;
	}
	/**
	 * Add a column to a table in the database
	 * @param string $table
	 * @param string $column
	 * @param string $defenition
	 * @return boolean
	 */
	public static function addColumn($table, $column, $definition) {
		return self::exec("ALTER TABLE `".$table."` ADD `".$column."` ".$definition);
	}
	/**
	 * Drop column from table in database
	 * @param string $table
	 * @param string $column
	 * @return boolean
	 */
	public static function dropColumn($table, $column) {
		return self::exec("ALTER TABLE `".$table."` DROP `".$column."`;");
	}
	/**
	 * Empty table in the database
	 * @param string $table
	 * @return int
	 */
	public static function emptyTable($table) {
		return self::delete($table);
	}
	/**
	 * Drop table in the database
	 * @param string $table
	 * @return boolean
	 */
	public static function dropTable($table) {
		return self::exec("DROP TABLE `".$table."`");
	}
	/**
	 * Delete records from a table
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return int
	 */
	public static function delete($table, $condition=null, $params=array()) {
		// Do we have a condition
		$where = '';
		if($condition) {
			// Add to where
			$where = ' WHERE ' . $condition;
		}

		$sql = "DELETE FROM `".$table."`{$where}";

		$query = self::prepare($sql);

		// Add in the params if we have any
		if(count($params)) {
			foreach($params as $k => $v) {
				$query->bindValue($k, $v);
			}
		}

		return $query->execute();
	}
	/**
	 * Insert records into a table
	 * @param string $table
	 * @param array $values
	 * @return int
	 */
	public static function insert($table, $data, $returnQuery=false) {
		$columns      = array();
		$placeholders = array();

		foreach ($data as $key => $val) {
			$columns[]      = "`".$key."`";
			$placeholders[] = ":$key";
		}

		$columns      = implode(', ', $columns);
		$placeholders = implode(', ', $placeholders);

		$sql   = "INSERT INTO `".$table."` ($columns) VALUES ($placeholders);";
		$query = self::prepare($sql);
		
		if($returnQuery) {
			foreach ($data as $key => $val) {
				$sql = str_replace(":$key", self::quote($val), $sql);
			}
			
			return $sql;
		}
		
		foreach ($data as $key => $val) {
			$query->bindValue(":$key", $val);
		}

		return $query->execute();
	}
	/**
	 * Replace records into a table
	 * @param string $table
	 * @param array $values
	 * @return int
	 */
	public static function replace($table, $data, $returnQuery=false) {
		$columns      = array();
		$placeholders = array();

		foreach ($data as $key => $val) {
			$columns[]      = "`".$key."`";
			$placeholders[] = ":$key";
		}

		$columns      = implode(', ', $columns);
		$placeholders = implode(', ', $placeholders);

		$sql   = "REPLACE INTO `".$table."` ($columns) VALUES ($placeholders);";
		$query = self::prepare($sql);
		
		if($returnQuery) {
			foreach ($data as $key => $val) {
				$sql = str_replace(":$key", self::quote($val), $sql);
			}
			
			return $sql;
		}
		
		foreach ($data as $key => $val) {
			$query->bindValue(":$key", $val);
		}

		return $query->execute();
	}
	/**
	 * Create new table in the database
	 * @param string $table
	 * @param array $values
	 * @return int
	 */
	public static function createTable($name, $columns, $props=null) {
		$dbColumns = array();
		foreach($columns as $key => $value) {
			$dbColumns[] = "\t`".$key."` $value";
		}

		$sql  = "CREATE TABLE `".$name."` (\n";
		$sql .= implode(",\n", $dbColumns);
		$sql .= "\n\t)";

		// Do we have properties
		if($props) {
			$sql .= ' '.$props;
		}

		// Finish
		$sql .= ';';

		$query = self::prepare($sql);
		return $query->execute();
	}
	/**
	 * Update records in a table
	 * @param string $table
	 * @param array $data
	 * @param string $condition
	 * @param array $params
	 * @return int
	 */
	public static function update($table, $data, $condition=null, $params=array()) {		
		// Prepare values
		foreach($data as $a => $b) {
			$values[] = "`$a`=:$a";
		}
		
		// Implode values
		$values = implode(', ', $values);

		// Do we have a condition
		$where = '';
		if($condition) {
			// Add to where
			$where = ' WHERE ' . $condition;
		}

		$sql = "UPDATE `".$table."` SET $values{$where}";

		$query = self::prepare($sql);

		foreach ($data as $key => $val) {
			$query->bindValue(":$key", $val);
		}

		// Add in the params if we have any
		if(count($params)) {
			foreach($params as $k => $v) {
				$query->bindValue($k, $v);
			}
		}
		return $query->execute();
	}
	
	/**
	 * Create SQL Statement
	 * @param string $table
	 * @param array $data
	 * @param string $condition
	 * @param array $params
	 * @return int
	 */
	public static function createSQL($table, $data, $condition=null, $params=array()) {		
		// Prepare values
		foreach($data as $a => $b) {
			$values[] = "`$a`=:$a";
		}
		
		// Implode values
		$values = implode(', ', $values);

		// Do we have a condition
		$where = '';
		if($condition) {
			// Add to where
			$where = ' WHERE ' . $condition;
		}

		$sql = "UPDATE `".$table."` SET $values{$where}";

		foreach ($data as $key => $val) {
			$sql = str_replace(":$key", self::quote($val), $sql);
		}

		// Add in the params if we have any
		if(count($params)) {
			foreach($params as $k => $v) {
				$sql = str_replace($k, self::quote($v), $sql);
			}
		}
		return $sql;
	}

	/**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param  string $sql
     * @param  integer $count
     * @param  integer $offset OPTIONAL
     * @return string
     */
     public static function limit($sql, $count, $offset = 0)
     {
        $count = intval($count);
        $offset = intval($offset);

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }

	/**
	 * Find all
	 *
	 */
	public static function findAll($sql, $values=array()) {
		$q = self::query($sql, $values);
		return self::fetchAll();
	}

	/**
	 * find one
	 *
	 */
	public static function findOne($sql, $values=array()) {
		$q = self::query($sql, $values);
		return self::fetch();
	}
}