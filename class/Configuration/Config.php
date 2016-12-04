<?php
namespace Configuration;

use Framework\Util\Filesystem\Directory;
use Framework\Util\GlobalArray;

class Config extends GlobalArray {
	protected static $_SOURCE = "aConfig";

	public static $CONFIG_PATH = "configuration/";
	public static $CONFIG_TEMPLATE_PATH = "configuration/templates/";

	public static function getConfigDirectory() {
		return new Directory(self::$CONFIG_PATH);
	}

	public static function getTemplateDirectory() {
		return new Directory(self::$CONFIG_TEMPLATE_PATH);
	}

	public static function getConfigFiles() {
		return array_merge(self::getConfigDirectory()->getContentsMatching("/^config[a-z_]*.php\$/"), self::getConfigDirectory()->getContentsMatching("/^latexconfig.php\$/"));
	}

	public static function getTemplateFiles() {
		return self::getTemplateDirectory()->getContentsMatching("/.tex\$/");
	}
}

