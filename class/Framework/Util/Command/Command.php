<?php

namespace Framework\Util\Command;

class Command {
	private $command = null;
	private $args = [];
	public function __construct($command, $args = []) {
		$this->command = $command;
		$this->args = $args;
	}

	final protected function getCommand() {
		return $this->command;
	}

	final protected function setCommand($c) {
		$this->command = $c;
	}

	final protected function getArgs() {
		return $this->args;
	}

	final protected function setArgs($args = []) {
		$this->args = $args;
	}

	final protected function addArg($arg) {
		$this->args[] = $arg;
	}

	final protected function run() {
		return static::exec($this->getCommand(), $this->getArgs());
	}

	public static function escape($arg) {
		return escapeshellarg($arg);
	}

	public static function exec($command, $args = []) {
		$output = [];
		foreach ($args as $arg) {
			$command .= " ".$arg;
		}
		exec($command, $output, $retval);
		$output = implode("\n", $output);
		return new Output($output, $retval);
	}
}