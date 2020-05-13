<?php

use DenisBeliaev\Build;
use PHPUnit\Framework\TestCase;
use Symfony\Component\{Filesystem\Filesystem, Finder\Finder};

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

    public function testHashedPaths()
    {
        $build = new Build(
            [
                'pages'        => '/tests/fixtures/html',
                'cacheBusting' => [
                    '/tests/fixtures/test.file'
                ]
            ]
        );

        $build->addPath('/tests/fixtures');

        $build->run();

        $finder = (new Finder())->files()->name('test.*.file')->in(getcwd() . '/build')->depth(0);
        $this->assertTrue($finder->hasResults());

        /** @var SplFileInfo $splFile */
        $splFile = iterator_to_array($finder, false)[0];
        $fileName = $splFile->getFileName();

        $this->assertContains($fileName, file_get_contents(getcwd() . '/build/tests/fixtures/html/template.html'));
    }

    protected function setUp()
    {
        (new Filesystem())->remove(getcwd() . '/build');
    }
}
