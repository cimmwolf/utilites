<?php
declare(strict_types=1);

namespace DenisBeliaev;

use Symfony\Component\{Filesystem\Filesystem, Finder\Finder};
use InvalidArgumentException;


class Build
{
	private $baseDir;
	private $paths = [];
	private $images = [];
	private $fs;
	/**
	 * @var string
	 */
	private $publicDir;
	/**
	 * @var string
	 */
	private $buildDir;

	public function __construct()
	{
		$this->baseDir = getcwd();
		$this->publicDir = $this->baseDir . '/public';
		$this->buildDir = $this->baseDir . '/build';

		$this->fs = new Filesystem();
	}

	public function run()
	{
		if (file_exists($this->buildDir)) {
			$this->fs->remove($this->buildDir);
		}

		if (file_exists($this->baseDir . '/public')) {
			rename($this->publicDir, $this->buildDir);
		} else {
			mkdir($this->buildDir);
		}

		foreach ($this->paths as $path) {
			$this->copyPath($path);
		}

		if (!empty($this->images)) {
			$this->convertWebP();
		}

		return true;
	}

	protected function convertWebP()
	{
		foreach ($this->images as $image) {
			$filename = $this->buildDir . $image;
			$im = imagecreatefromwebp($filename);

			$newFilename = preg_replace('~(.*)\.webp$~', '$1.jpg', $filename);
			imagejpeg($im, $newFilename, 88);
			imagedestroy($im);
		}
	}

	public function addImages($path)
	{
		$this->addPath($path);

		$finder = new Finder();
		$finder->files()->in($this->baseDir . $path)->name('*.webp');
		foreach ($finder as $image) {
			$this->images[] = str_replace($this->baseDir, '', $image->getRealPath());
		}
	}

	public function addPath($path)
	{
		if (!in_array($path, $this->paths)) {
			$this->paths[] = $path;
		}
	}

	private function copyPath($path)
	{
		$currentName = $this->baseDir . $path;
		$newName = $this->buildDir . $path;
		if (file_exists($currentName)) {
			if (is_dir($currentName)) {
				$this->fs->mirror($currentName, $newName);
			} else {
				$this->fs->copy($currentName, $newName);
			}
		} else {
			throw new InvalidArgumentException("PHP build: No such directory: $currentName" . PHP_EOL);
		}
	}
}
