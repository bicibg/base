<?php

namespace Framework\Util\Filesystem;

class File extends AbstractFSNode {
	/**
	 * Returns the contents of the file
	 * @return string
	 */
	public function getContents() {
		return file_get_contents($this->getPath());
	}

	/**
	 * Generates a new temp file
	 * @param string $postfix
	 * @return File
	 */
	public static function createTemporaryFile($postfix = "") {
		return Directory::getTemporaryDirectory()->addFile(self::generateRandomFilename($postfix));
	}

	/**
	 * Sets file contents (wrapper)
	 * @param string $contents
	 * @param int $flags
	 */
	protected function setFileContents($contents, $flags = 0) {
		file_put_contents($this->getPath(), $contents, $flags);
	}

	/**
	 * Overwrites the contents of a file
	 * @param string $cont
	 */
	public function setContents($cont) {
		$this->setFileContents($cont);
	}

	/**
	 * Appends the contents to the file
	 * @param string $cont
	 */
	public function appendContents($cont) {
		$this->setFileContents($cont, FILE_APPEND);
	}

	/**
	 * Prepends the $cont to the file
	 * @param string $cont
	 */
	public function prependContentes($cont) {
		$this->setFileContents($cont.$this->getContents());
	}

	/**
	 * Requires a file
	 */
	public function requireMe() {
		require($this->getPath());
	}

	/**
	 * Require once a file
	 */
	public function requireOnce() {
		require_once($this->getPath());
	}

	/**
	 * Deletes the file
	 * @throws \Exception
	 */
	public function remove() {
		if (unlink($this->getPath())) return;
		throw new \Exception("Couldn't remove file");
	}

	/**
	 * Creates a file
	 * @param string $name
	 * @return File
	 * @throws \Exception
	 */
	public static function createFile($name) {
		if (!touch($name)) throw new \Exception("Couldn't create file!");
		return new File($name);
	}
}
