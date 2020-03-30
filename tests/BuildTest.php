<?php

use DenisBeliaev\Build;
use PHPUnit\Framework\TestCase;

class BuildTest extends TestCase
{
	public function testAddPath()
	{
		$path = '/tests/fixtures/test.file';

		$build = new Build();
		$build->addPath($path);
		$build->run();

		$this->assertFileExists(getcwd() . '/build/' . $path);
	}

	public function testWebP()
	{
		$build = new Build();

		$build->addImages('/tests/fixtures/webp');

		$build->run();

		$dir = getcwd() . '/build/tests/fixtures/webp';
		$this->assertDirectoryExists($dir);

		$files = [
			'/1.sm.webp',
			'/2.sm.webp',
			'/subfolder/3.sm.webp',
			'/1.sm.jpg',
			'/2.sm.jpg',
			'/subfolder/3.sm.jpg',
		];

		foreach ($files as $file) {
			$this->assertFileExists($dir . $file);
		}
	}

	protected function setUp()
	{
		(new Symfony\Component\Filesystem\Filesystem())->remove(getcwd() . '/build');
	}
}
