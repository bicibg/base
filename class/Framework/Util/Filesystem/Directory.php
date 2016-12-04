<?php

namespace Framework\Util\Filesystem;

class Directory extends AbstractFSNode {
	private static $TMP_FOLDER = "/tmp/";

	public function __construct($name) {
		parent::__construct($name);
		if (!$this->isDir()) throw new \Exception("Not a directory");
	}

	/**
	 * Returns the temporary folder location
	 * @return Directory
	 */
	public static function getTemporaryDirectory() {
		return new Directory(self::$TMP_FOLDER);
	}

	/**
	 * Returns directory contents
	 * @return array
	 */
	public function getContents() {
		return scandir($this->getPath());
	}

	/**
	 * Creates a new file inside the directory
	 * @param string $file_name
	 * @return File
	 * @throws \Exception
	 */
	public function addFile($file_name) {
		$full_path = $this->getPath().DIRECTORY_SEPARATOR.$file_name;
		return File::createFile($full_path);
	}

	public function in($file_name) {
		$full_path = $this->getPath().DIRECTORY_SEPARATOR.$file_name;
		return new File($full_path);
	}

	/**
	 * Creates a new directory inside the current one
	 * @param string $dirname
	 * @return Directory
	 */
	public function addDirectory($dirname) {
		$full_path = $this->getPath().DIRECTORY_SEPARATOR.$dirname;
		return Directory::createDirectory($full_path);
	}

	/**
	 * Completely removes the directory
	 * @throws \Exception
	 */
	public function remove() {
		if (rmdir($this->getPath())) return;
		throw new \Exception("Couldn't remove folder");
	}

	/**
	 * New temporary directory
	 * @return Directory
	 * @throws \Exception
	 */
	public static function createTemporaryDirectory() {
		return self::createDirectory(self::getTemporaryDirectory(), self::generateRandomFilename());
	}

	/**
	 * Creates a directory
	 * @param string $dir_name
	 * @return Directory
	 * @throws \Exception
	 */
	protected static function createDirectory($dir_name) {
		if (!mkdir($dir_name, 0777, true)) throw new \Exception("Couldn't create the directory");
		return new Directory($dir_name);
	}

	/**
	 * Returns the contents matching the regex $regex
	 * @param string $regex
	 * @return array
	 */
	public function getContentsMatching($regex) {
		$file_list = [];
		foreach ($this->getContents() as $file) {
			if (!preg_match($regex, $file)) continue;
			$file = $this->getPath().DIRECTORY_SEPARATOR.$file;
			$file_list[] = self::pathIsDir($file) ? new Directory($file) : new File($file);
		}
		return $file_list;
	}

	/**
	 * Calculates whether the given node is inside the folders path
	 * @param AbstractFSNode $node
	 * @return bool
	 */
	public function inPath(AbstractFSNode $node) {
		$prefix = $this->getPath();
		if (substr($node->getPath(), 0, strlen($prefix)) == $prefix) {
			return true;
		}
		return false;
	}

	/**
	 * Calculates the relative offset
	 * @param AbstractFSNode $node
	 * @return string
	 */
	public function relativeTo(AbstractFSNode $node) {
		$back_dir = $this;
		$iterations = 0;
		while (!$back_dir->inPath($node)) {
			$back_dir = $back_dir->getParentDirectory();
			$iterations++;
		}

		$compiled_path = substr($node->getPath(), strlen($back_dir->getPath()) + 1);

		$back = str_repeat("..".DIRECTORY_SEPARATOR, $iterations).$compiled_path;

		return $back;
	}
}
