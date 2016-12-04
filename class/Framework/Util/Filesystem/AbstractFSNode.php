<?php

namespace Framework\Util\Filesystem;
use Framework\Configuration\BootstrapConfig;

/**
 * File system item
 * Class AbstractFSNode
 * @package Util\Filesystem
 */
abstract class AbstractFSNode {
	private $path = null;

	/**
	 * Takes a file/directory path (string)
	 * @param string $file_path
	 * @throws \Exception
	 */
	public function __construct($file_path) {
		$file_path = self::standardizePath($file_path);
		if (!self::exists($file_path)) {
			throw new \Exception("File does not exist: \"$file_path\"");
		}
		$this->path = realpath($file_path);
	}

	/**
	 * Standardizes the path. Relative paths become absolute (relative to the document root)
	 * @param string $path
	 * @return string
	 */
	protected static function standardizePath($path) {
		return self::pathIsRelative($path) ? self::getDocumentRoot().DIRECTORY_SEPARATOR.$path : $path;
	}

	/**
	 * Evaluates whether the given path is absolute or relative
	 * @param $path
	 * @return bool
	 */
	protected static function pathIsRelative($path) {
		return !preg_match("|^".DIRECTORY_SEPARATOR."|", $path);
	}

	/**
	 * Returns the document root folder (buggy)
	 * @return Directory
	 */
	protected static function getDocumentRoot() {
		return BootstrapConfig::getParam("document_root", $_SERVER['DOCUMENT_ROOT']);
	}

	/**
	 * Generates a random file name
	 * @param string $postfix
	 * @return string
	 */
	public static function generateRandomFilename($postfix = "") {
		return sha1(uniqid("", true)).$postfix;
	}

	/**
	 * Checks the existence of the path.
	 * @param string $name
	 * @return bool
	 */
	public static function exists($name) {
		return file_exists($name);
	}

	/**
	 * Checks if the given entity is a directory
	 * @return bool
	 */
	public function isDir() {
		return self::pathIsDir($this->getPath());
	}

	/**
	 * Checks whether a given path is a directory
	 * @param string $item
	 * @return bool
	 */
	public static function pathIsDir($item) {
		return is_dir($item);
	}

	/**
	 * Returns the path of the file/folder (without a dir-separator in the end)
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Gets the base name of the entity (=> only the name of the directory/file)
	 * @return string
	 */
	public function getBaseName() {
		return basename($this->getPath());
	}

	/**
	 * Returns the modification time of the entity
	 * @return int
	 */
	public function getMTime() {
		return filemtime($this->getPath());
	}

	/**
	 * Returns the file size
	 * @return int
	 */
	public function getSize() {
		return filesize($this->getPath());
	}

	/**
	 * Returns the parent directory of the entity
	 * @return Directory
	 */
	public function getParentDirectory() {
		return new Directory(dirname($this->getPath()));
	}

	/**
	 * Returns the path of the fs-entity inside the folder $dir
	 * @param Directory $dir
	 * @return string
	 */
	protected function pathIn(Directory $dir) {
		return $dir->getPath().DIRECTORY_SEPARATOR.$this->getBaseName();
	}

	/**
	 * Renames the given entity
	 * @param $new_name
	 * @throws \Exception
	 */
	public function rename($new_name) {
		$new_path = $this->getParentDirectory()->getPath().DIRECTORY_SEPARATOR.$new_name;
		if (!rename($this->getPath(), $new_path)) throw new \Exception("Couldn't rename the file $this to $new_path");
		$this->path = $new_path;
	}

	/**
	 * Copies the file to the directory $dir
	 * @param Directory $dir
	 * @return File
	 * @throws \Exception
	 */
	public function copyTo(Directory $dir) {
		if (!copy($this->getPath(), $this->pathIn($dir))) throw new \Exception("Couldn't copy the file $this to $dir");
		return new File($this->pathIn($dir));
	}

	/**
	 * Checks whether the $path parameter contains any /../
	 * @param $path
	 * @return boolean
	 */
	public static function isSafe($path) {
		return preg_match("|".DIRECTORY_SEPARATOR."\\.\\.".DIRECTORY_SEPARATOR."|", $path);
	}

	/**
	 * Moves a fs-entity to the directory
	 * @param Directory $dir
	 * @throws \Exception
	 */
	public function moveTo(Directory $dir) {
		$new_path = $this->pathIn($dir);
		if (!rename($this->getPath(), $new_path)) throw new \Exception("Couldn't rename the file $this to $new_path");
		$this->path = $new_path;
	}

	/**
	 * We normally want to see a string when converting
	 * @return string
	 */
	public function __toString() {
		return $this->getPath();
	}

	/**
	 * Removes the fs-entity
	 * @return void
	 */
	abstract public function remove();
}
