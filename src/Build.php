<?php
declare(strict_types=1);

namespace DenisBeliaev;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


class Build
{
	protected $dir;
	protected $paths = [];

	public function __construct()
	{
		$this->dir = getcwd();
	}

	function addPath($path)
	{
		if (!in_array($path, $this->paths)) {
			$this->paths[] = $path;
		}
	}

	function run()
	{
		$buildFolder = $this->dir . '/build';
		$publicFolder = $this->dir . '/public';

		if (file_exists($buildFolder)) {
			$this->deleteFolder($buildFolder);
		}

		if (file_exists($this->dir . '/public')) {
			rename($publicFolder, $buildFolder);
		} else {
			mkdir($buildFolder);
		}

		foreach ($this->paths as $path) {
			$oldName = $this->dir . $path;
			$newName = $buildFolder . $path;
			if (file_exists($oldName)) {
				if (!file_exists(dirname($newName))) {
					mkdir(dirname($newName), 0777, true);
				}
				rename($oldName, $newName);
			} else {
				echo "PHP build: No such directory: $oldName" . PHP_EOL;
			}
		}

		echo 'PHP build: Complete' . PHP_EOL;
	}

	protected function deleteFolder($dir)
	{
		$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
			RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $file) {
			if ($file->isDir()) {
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir($dir);
	}
}
