<?php
namespace Framework\Database;

use PDO;
use PDOException;
use Framework\Util\Filesystem\File;
use Framework\Util\Command\Command;
use Framework\Util\Command\Diff;
use Configuration\Config;

/**
 * Class Database
 * @package Database
 */

abstract class AbstractDatabase implements Database{
	private static $pdo = null;
	private $db = null;

	public function __construct(){
		$this->db = self::ensureConnected();
	}

	protected static function ensureConnected(){
		if (isset(self::$pdo)) {
			return self::$pdo;
		}

		try{
			$dsn = self::getType().":host=".self::getHost().";dbname=".self::getDatabaseName().";charset=".self::getCharset();
			$opt = array(
					PDO::ATTR_EMULATE_PREPARES => true,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				);
			self::$pdo = new PDO($dsn,self::getUser(),self::getPassword(),$opt);
			return self::$pdo;

		}catch(PDOException $e){
			print_r($e);
		}
	}

	public static function query($query,$placeholders = []){
		return self::_query($query,$placeholders,true);
	}

	public static function one($query, $placeholders = []) {
		$arr = self::query($query, $placeholders);

		if(isset($arr[0]))
			return $arr[0];
		else
			return [];
	}

	public static function _query($query,$placeholders,$fetchall=false){
		$driver = self::ensureConnected();
		$statement = $driver->prepare($query);
		$statement->execute($placeholders);
		if(!$fetchall){
			return $statement->rowCount();
		}
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	public static function getCharset(){
		return Config::getParam(["database","charset"]);
	}

	public static function getUser(){
		return Config::getParam(["database","user"]);
	}

	public static function getPassword(){
		return Config::getParam(["database","pass"]);

	}

	public static function getType(){
		return Config::getParam(["database","type"]);

	}

	public static function getDatabaseName(){
		return Config::getParam(["database","dbname"]);

	}

	public static function getHost(){
		return Config::getParam(["database","host"]);
	}
	public static function lastInsert(){
		$driver = self::ensureConnected();
		return $driver->lastInsertId();
	}

	public static function execute($query,$placeholders = []){
		return self::_query($query,$placeholders);
	}

	public static function quote($string) {
		$driver = self::ensureConnected();

		return $driver->quote($string);
	}

	public static function beginTransaction() {
		$driver = self::ensureConnected();
		return $driver->beginTransaction();
	}

	public static function commit() {
		$driver = self::ensureConnected();

		return $driver->commit();
	}

	public static function rollBack() {
		$driver = self::ensureConnected();

		return $driver->rollBack();
	}

	public static function compareToFile(File $file) {
		$oDiff = new Diff();
		return $oDiff->diffFile($file, static::getDatabaseName())->getOutput();
	}

	public static function cliExec($string) {
		$oFile = File::createTemporaryFile();
		$oFile->setContents($string);

		$ret = Command::exec("mysql ".
			" -h ".static::getHost().
			" -u ".static::getUser().
			(static::getPassword() ? " -p".static::getPassword() : "").
			" ".static::getDatabaseName()." < ".$oFile->getPath());

		$oFile->remove();

		return $ret->getOutput();
	}
	public static function dumpStructure(File $file) {
		$file->setContents(static::getStructure());
	}

	public static function getStructure() {
		$ret = Command::exec("mysqldump --skip-comments --single-transaction".
			" -h ".static::getHost().
			" -u ".static::getUser().
			(static::getPassword() ? " -p".static::getPassword() : "").
			" -d ".static::getDatabaseName().
			" | sed 's/ AUTO_INCREMENT=[0-9]*\\b//'");
		return $ret->getOutput();
	}
}
?>
