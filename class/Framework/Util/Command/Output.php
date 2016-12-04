<?php
namespace Framework\Util\Command;

class Output {
	private $output = null;
	private $retval = null;
	public function __construct($output, $ret_val) {
		$this->output = $output;
		$this->retval = $ret_val;
	}

	public function getReturnValue() {
		return $this->retval;
	}

	public function getOutput() {
		return $this->output;
	}
}