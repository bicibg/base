<?php

namespace Framework\Util;

class TrObject {
	private $string;
	private $arg_counter;

	public function __construct($str) {
		$this->arg_counter = 1;
		$this->string = $str;
	}

	public function arg($arg) {
		$this->string = preg_replace('/\\%'.$this->arg_counter.'(?![0-9])/', $arg, $this->string, 1);
		$this->arg_counter++;

		return $this;
	}

	public function __toString() {
		return $this->string;
	}
}