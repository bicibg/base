<?php

namespace Framework\Util\Command;

use Framework\Util\Filesystem\File;

class Prompt {
	private $output = null;
	private $command = null;
	private $additional = null;
	private $lock = false;

	public $data = [];

	protected static $_LOCK_FILE = "/tmp/chronikos_lock.tmp";

	public function __construct($command, $data, $additional = null) {
		$this->command = $command;
		$this->data = $data;
		$this->additional = $additional;
	}

	protected function lock() {
		if (!File::exists(static::$_LOCK_FILE)) {
			File::createFile(static::$_LOCK_FILE);
		} else {
			throw new \Exception("Partner is locked. Sorry.");
		}
	}

	protected function unlock() {
		if (File::exists(static::$_LOCK_FILE)) {
			(new File(static::$_LOCK_FILE))->remove();
		}
	}

	protected function requireData($data_index) {
		if (!is_array($data_index)) {
			$data_index = [$data_index];
		}

		foreach ($data_index as $index) {
			if (!isset($this->data[$index])) {
				throw new \Exception("Error! Parameter \"$index\" needs to be specified.");
			}
		}
	}

	public function getAdditional() {
		return $this->additional;
	}

	final public function run() {
		$this->executeCommand($this->command);
	}

	protected function preCheck() {}

	final protected function executeCommand($command) {
		$function_to_call = "do_$command";
		$this->$function_to_call();
	}

	final protected function setOutput($output) {
		$this->output = $output;
	}

	final public function getOutput() {
		return $this->output;
	}

	final public static function bulk(array $input_arr, $additional_info = null) {
		$ret_val = [];
		$class = get_called_class();
		foreach ($input_arr as $index => $var) {
			$oMain = new $class($var['method'], $var, $additional_info);
			$oMain->preCheck();
			$oMain->run();
			$ret_val[$index]['output'] = $oMain->getOutput();
		}

		return $ret_val;
	}
}
