<?php

class RecordExistsExpectation extends SimpleExpectation {
	private $table = "";
	private $record = array();

	function __construct($table, array $record, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		parent::__construct($message);
	}

	function test($record_number) {
		return $record_number > 0;
	}

	function testMessage($record_number) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ". ($value === "" ? "''" : $value);
		}

		return "Record [". join(', ', $expected)."] not found in table [". $this->table."]";
	}
}

class RecordNotExistsExpectation extends SimpleExpectation {
	private $table = '';
	private $record = array();

	function __construct($table, array $record, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		parent::__construct($message);
	}

	function test($record_number) {
		return $record_number <= 0;
	}

	function testMessage($record_number) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ". ($value === "" ? "''" : $value);
		}

		return "Record [". join(", ", $expected)."] found in table [". $this->table."]";
	}
}

class CountRecordExpectation extends SimpleExpectation {
	private $table = '';
	private $record = array();
	private $count = 0;

	function __construct($table, array $record, $count = 0, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		$this->count = $count;
		parent::__construct($message);
	}

	function test($count) {
		return $this->count == $count;
	}

	function testMessage($count) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ". ($value == "" ? "''" : $value);
		}

		return $count."/". $this->count." record(s) [". join(", ", $expected)."] found in table [". $this->table."]";
	}
}

class RecordNotDuplicateExpectation extends SimpleExpectation {
	private $table = '';
	private $record = array();

	function __construct($table, array $record, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		parent::__construct($message);
	}

	function test($count) {
		return $count <= 1;
	}

	function testMessage($count) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ".($value == "" ? "''" : $value);
		}

		return "Record [". join(", ", $expected)."] is duplicate ". $count." time(s) in table [". $this->table."]";
	}
}

class TableHasSizeExpectation extends SimpleExpectation {
	private $table = '';
	private $size = array();


	function __construct($table, $size, $message = '%s') {
		$this->table = $table;
		$this->size = $size;
		parent::__construct($message);
	}

	function test($size) {
		return $size == $this->size;
	}

	function testMessage($size) {
		return "Table [". $this->table."] has not size [". $this->size."], real size is [". $size."]";
	}
}

abstract class TableTestCase extends UnitTestCase {
	protected $sql = null;
	protected $backups = array();

	function __construct($label = false, $sql = null) {
		parent::__construct($label);
		if ($sql=== null) {
			$sql = new MySQL($GLOBALS['config']->db());
		}
		$this->sql = $sql;
	}

	function resetDatabase() {
		$this->sql->file(dirname(__FILE__)."/../../../admin/install/install.sql");
	}

	function assertTableHasSize($table, $size) {
		$q = "SELECT COUNT(*) AS nb FROM ". $table;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$this->assert(new TableHasSizeExpectation($table, $size), $row['nb']);
	}

	function assertRecordsExist($table, array $records) {
		foreach ($records as $record) {
			$q = "SELECT COUNT(*) AS nb FROM ". $table." WHERE ".join(" AND ", $this->getWhere($record));
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$this->assert(new RecordExistsExpectation($table, $record), $row['nb']);
		}
	}

	function assertRecordsNotExist($table, array $records) {
		foreach ($records as $record) {
			$q = "SELECT COUNT(*) AS nb FROM ". $table." WHERE ".join(" AND ", $this->getWhere($record));
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$this->assert(new RecordNotExistsExpectation($table, $record), $row['nb']);
		}
	}

	function assertRecordExists($table, array $record) {
		$q = "SELECT COUNT(*) AS nb FROM ". $table." WHERE ".join(" AND ", $this->getWhere($record));
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$this->assert(new RecordExistsExpectation($table, $record), $row['nb']);
	}

	function assertRecordNotExists($table, array $record) {
		$q = "SELECT COUNT(*) AS nb FROM ". $table." WHERE ".join(" AND ", $this->getWhere($record));
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$this->assert(new RecordNotExistsExpectation($table, $record), $row['nb']);
	}

	function assertRecordsNotDuplicate($table, array $records) {
		foreach ($records as $record) {
			$q = "SELECT COUNT(*) AS nb FROM ". $table." WHERE ".join(" AND ", $this->getWhere($record));
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$this->assert(new RecordNotDuplicateExpectation($table, $record), $row['nb']);
		}
	}

	function assertCountRecords($table, array $records, $count) {
		foreach ($records as $record) {
			$q = "SELECT COUNT(*) AS nb FROM ". $table." WHERE ".join(" AND ", $this->getWhere($record));
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$this->assert(new CountRecordExpectation($table, $record, $count), $this->row['nb']);
		}
	}

	function assertSameTable($table, $records) {
		$recordSize = sizeof($records);
		$q = "SELECT COUNT(*) AS nb FROM ". $table;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$tableSize = $row['nb'];

		if ($recordSize != $tableSize) {
			$this->assert(new TableHasSizeExpectation($table, $recordSize), $tableSize);
		} else {
			$this->assertRecordsExists($table, $records);
		}
	}

	function getRecords($table, array $columns = array()) {
		$records = array();

		list($recordSet) = $this->sql->query("SELECT ".(sizeof($columns) == 0 ? '*' : join(', ', $columns))." FROM ".$table);

		while ($record = $this->sql->fetch($recordSet)) {
			$records[] = $record;
		}

		return $records;
	}

	function truncateTable($table) {
		$this->sql->query("TRUNCATE ". $table);
	}

	function truncateTables() {
		$tables = func_get_args();

		foreach ($tables as $table) {
			$this->truncateTable($table);
		}
	}

	function insertIntoTable($table, array $records) {
		foreach ($records as $record) {
			$columns = array();
			$values = array();

			foreach ($record as $column => $value) {
				$columns[] = $column;
				$values[] = $this->sql->quote($value);
			}

			$this->sql->query("INSERT INTO ".$table." (".join(",", $columns).") VALUES (".join(", ", $values).")");
		}
	}

	function getLastInsertId() {
		return $this->sql->insert_id();
	}

	function insertIntoTables() {
		$tableRecords = func_get_args();

		foreach ($tableRecords as $table => $records) {
			$this->insertIntoTables($table, $records);
		}
	}

	function backupTables() {
		$tables = func_get_args();

		foreach ($tables as $table) {
			$this->backups[$table] = $this->getRecords($table);
		}
	}

	function restoreTables() {
		foreach ($this->backups as $table => $records) {
			$this->truncateTable($table);
			$this->insertIntoTable($table, $records);
		}

		$this->backups = array();
	}

	private function getWhere($record) {
		$where = array();

		foreach ($record as $column => $value) {
			switch (true) {
				case is_string($value):
					$where[] = $column." = ".$this->sql->quote($value);
					break;
				case $value === null:
					$where[] = $column." IS NULL";
					break;
				default:
					$where[] = $column." = ".$value;
			}
		}

		return $where;
	}
}

