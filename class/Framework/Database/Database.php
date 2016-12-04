<?php
namespace Framework\Database;

/**
 * Class Database
 * @package Database
 */

interface Database{
	public static function query($query,$placeholders = []);
	public static function lastInsert();
	public static function execute($query,$placeholders = []);
	public static function getDatabaseName();
	public static function getCharset();
	public static function getUser();
	public static function getPassword();
	public static function getType();
	public static function getHost();
}
?>
