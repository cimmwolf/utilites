<?php
declare(strict_types=1);

namespace DenisBeliaev;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


class Build
{
	protected $dir;
	protected $folders = [];

	public function __construct()
	{
		$this->dir = getcwd();
	}

	function addFolder($folder)
	{
		if (!in_array($folder, $this->folders)) {
			$this->folders[] = $folder;
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

		foreach ($this->folders as $folder) {
			$oldName = $this->dir . $folder;
			$newName = $buildFolder . $folder;
			if (file_exists($oldName)) {
				if (count(explode('/', $newName)) > 1) {
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
