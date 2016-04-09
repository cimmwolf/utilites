<?php
/**
 * Author: Denis Beliaev <cimmwolf@gmail.com>
 */

namespace DenisBeliaev;

/**
 * Class adaptiveImg
 * @package Utilites
 */
class AdaptiveImg
{
    public $root;

    /**
     * adaptiveImg constructor.
     * @param string $root path to project's root
     */
    public function __construct($root = '')
    {
        if (empty($root))
            $root = realpath(__DIR__ . '/../../../..');
        $this->root = $root;
    }

    /** Create img tag with parameters srcset (type w) and sizes
     *
     * @param array $src
     * @param string $alt alt attribute
     * @param string $sizes sizes attribute
     * @param array $widths widths of image
     * @param array $htmlOptions other attributes of the img tag in attribute => value format
     * @param $filePath
     * @param int $maxWidth
     * @return string HTML img tag
     */
    public function typeW($src, $alt, $sizes, $widths, $htmlOptions = [], $filePath = '', $maxWidth = 1920)
    {
        $srcSet = [];

        if (empty($filePath) AND $src[0] == '/')
            $filePath = realpath($this->root . $src);

        $imgInfo = @getimagesize($filePath);

        if ($imgInfo !== false)
            $maxWidth = $imgInfo[0];

        foreach ($widths as $width) {
            $widths[] = $width * 2;
            $widths[] = $width * 3;
        }

        if (!in_array($maxWidth, $widths))
            $widths[] = $maxWidth;

        $widths = array_unique($widths);
        sort($widths);

        foreach ($widths as $width)
            if ($width <= $maxWidth)
                $srcSet[] = preg_replace('#^(.*?)\.(jpe?g|png|gif)$#', "$1@{$width}x-.$2 {$width}w", $src);
        $htmlOptions['srcset'] = implode(', ', $srcSet);
        $htmlOptions['sizes'] = $sizes;

        $attrString = '';
        foreach ($htmlOptions as $attr => $value) {
            if (!empty($attrString))
                $attrString .= ' ';
            $attrString .= "$attr=\"$value\"";
        }

        return "<img src=\"$src\" alt=\"$alt\" $attrString>";
    }
}