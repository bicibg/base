<?php

namespace Framework\Util;

abstract class GlobalArray {
	protected static $_SOURCE = "";
	
	public final static function getParams() {
		return self::_getParam([]);
	}

	public final static function hasParam($param) {
		try {
			self::_getParam(is_array($param) ? $param : [$param]);
			return true;
		} catch (\Exception $_) {
			return false;
		}
	}

	public final static function getParam($param, $default = false) {
		try {
			return self::_getParam(is_array($param) ? $param : [$param]);
		} catch (\Exception $_) {
			return $default;
		}
	}

	private static function _getParam(array $param) {
		$source = static::$_SOURCE;
		global $$source;

		$intermediate_value = $$source;
		foreach ($param as $arg) {
			if (!isset($intermediate_value[$arg])) throw new \Exception("bump");
			$intermediate_value = $intermediate_value[$arg];
		}

		return $intermediate_value;
	}

	public final static function setParam($param, $value) {
		self::_setParam($param, $value);
	}

	private static function _setParam($param, $value) {
		$source = static::$_SOURCE;
		global $$source;
		if(isset($source[$param])){
			$source[$param] = $value;
		}
	}
}
