<?php

declare(strict_types=1);

namespace DenisBeliaev;

use InvalidArgumentException;
use Symfony\Component\{Filesystem\Filesystem, Finder\Finder};


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
    /**
     * @var array
     */
    private $config = [
        'pages'        => '/dist/pages',
        'cacheBusting' => []
    ];

    public function __construct($config = null)
    {
        if (!empty($config)) {
            $this->config = array_replace_recursive($this->config, $config);
        }

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

        $this->cacheBusting();

        return true;
    }

    private function copyPath($path)
    {
        $currentName = $this->baseDir . $path;
        $newPathname = $this->buildDir . $path;
        if (file_exists($currentName)) {
            if (is_dir($currentName)) {
                $this->fs->mirror($currentName, $newPathname);
            } else {
                $this->fs->copy($currentName, $newPathname);
            }
        } else {
            throw new InvalidArgumentException("PHP build: No such directory: $currentName" . PHP_EOL);
        }
    }

    private function convertWebP()
    {
        foreach ($this->images as $image) {
            $filename = $this->buildDir . $image;
            $im = imagecreatefromwebp($filename);

            $newFilename = preg_replace('~(.*)\.webp$~', '$1.jpg', $filename);
            imagejpeg($im, $newFilename, 88);
            imagedestroy($im);
        }
    }

    private function cacheBusting()
    {
        foreach ($this->config['cacheBusting'] as $oldPath) {
            $currentPathname = $this->buildDir . $oldPath;

            if (!is_file($currentPathname)) {
                throw new InvalidArgumentException("PHP build: $currentPathname must be regular file" . PHP_EOL);
            }

            $hash = hash_file('crc32', $currentPathname);

            $pathInfo = pathinfo($oldPath);
            $newPath = '/' . implode('.', [$pathInfo['filename'], $hash, $pathInfo['extension']]);
            $newPathname = $this->buildDir . $newPath;

            if (file_exists($currentPathname)) {
                $this->fs->rename($currentPathname, $newPathname);
                $finder = (new Finder())
                    ->files()
                    ->name('*.html')
                    ->in($this->buildDir . $this->config['pages']);
                foreach ($finder as $splFileInfo) {
                    $content = str_replace(
                        ['src="' . $oldPath . '"', 'href="' . $oldPath . '"'],
                        ['src="' . $newPath . '"', 'href="' . $newPath . '"'],
                        $splFileInfo->getContents()
                    );
                    file_put_contents($splFileInfo->getPathname(), $content);
                }
            } else {
                throw new InvalidArgumentException("PHP build: No such directory: $currentPathname" . PHP_EOL);
            }
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
}
