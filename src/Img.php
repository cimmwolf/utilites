<?php
/**
 * Author: Denis Beliaev <cimmwolf@gmail.com>
 */

namespace DenisBeliaev;

class Img
{
    /** Create img tag with parameters srcset (type w) and sizes
     *
     * @param array $source [src, *path to file* or *maximum width of image*]
     * @param string $alt alt attribute
     * @param string $sizes sizes attribute
     * @param array $widths widths of image
     * @param array $htmlOptions other attributes of the img tag in attribute => value format
     *
     * @return string HTML img tag
     */
    static function w($source, $alt, $sizes, $widths, $htmlOptions = [])
    {
        $srcSet = [];
        if(is_int($source[1]))
            $realWidth = $source[1];
        else {
            $imgInfo = @getimagesize(__DIR__ . trim($source[1]));
            $realWidth = 1920;
            if ($imgInfo !== false)
                $realWidth = $imgInfo[0];
        }
        $src = $source[0];

        foreach ($widths as $width) {
            $widths[] = $width * 2;
            $widths[] = $width * 3;
        }

        if (!in_array($realWidth, $widths))
            $widths[] = $realWidth;

        $widths = array_unique($widths);
        sort($widths);

        foreach ($widths as $width)
            if ($width <= $realWidth)
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