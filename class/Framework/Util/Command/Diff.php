<?php

namespace Framework\Util\Command;

use Configuration\Config;
use Framework\Util\Filesystem\File;
use Framework\Util\Random;

class Diff extends Command {
	private $tmp_db = null;
	public function __construct() {
		parent::__construct(Config::getParam(["mysqldiff-bin"],"mysql-diff-structure"));
	}

	public function __destruct() {
		$this->dropTemporaryDatabase();
	}

	private function getReadInCommand($database = "") {
		return "mysql -u root $database ";
	}

	private function readInSql($sql) {
		Command::exec("echo ".self::escape($sql)." | ".$this->getReadInCommand());
	}

	public function diff($db1, $db2) {
		$this->prepareArgs($db1, $db2);
		return $this->run();
	}

	private function _diffFile(File $file, $db1, $db2) {
		$this->readIntoTemporaryDatabase($file);
		$return = $this->diff($db1, $db2);
		$this->dropTemporaryDatabase();
		return $return;
	}

	private function readIntoTemporaryDatabase(File $file) {
		Command::exec($this->getReadInCommand($this->tmp_db)." < {$file->getPath()}");
	}

	public function diffFile(File $file, $database) {
		$this->createTemporaryDatabase();
		return $this->_diffFile($file, $database, $this->tmp_db);
	}

	public function rDiffFile(File $file, $database) {
		$this->createTemporaryDatabase();
		return $this->_diffFile($file, $this->tmp_db, $database);
	}

	private function prepareArgs($db1, $db2) {
		$this->setArgs([
			"-u root",
			$db1,
			$db2,
		]);
	}

	public function createTemporaryDatabase() {
		if ($this->tmp_db) return;
		$this->tmp_db = Random::getUniqueId("tmp_");
		$this->readInSql("CREATE DATABASE IF NOT EXISTS {$this->tmp_db};");
	}

	public function dropTemporaryDatabase() {
		if (!$this->tmp_db) return;
		$this->readInSql("DROP DATABASE {$this->tmp_db};");
		$this->tmp_db = null;
	}
}