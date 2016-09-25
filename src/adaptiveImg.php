<?php
/**
 * Author: Denis Beliaev <cimmwolf@gmail.com>
 */

namespace DenisBeliaev;

/**
 * Class adaptiveImg
 * @package Utilities
 */
class AdaptiveImg
{
    private $info;
    private $alt;

    /**
     * AdaptiveImg constructor.
     * @param string $url
     * @param string $alt
     * @param string $path
     * @param array $info
     * @throws \Exception
     */
    public function __construct($url, $alt, $path = '', $info = [])
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->info = $info;

        if (empty($path)) {
            $parsedUrl = parse_url($url);
            if (!isset($parsedUrl['path']))
                throw new \Exception("Can't determine path");
            $path = realpath(__DIR__ . '/../../../..') . $parsedUrl['path'];
        }

        if (!isset($info[0]) OR !isset($info[1])) {
            $this->info = @getimagesize($path);
            if (empty($this->info))
                $this->info = [1920, 1080];
        }
    }

    /** Create img tag with parameters srcset (type w) and sizes
     *
     * @param string $sizes sizes attribute
     * @param array $widths widths of image
     * @param array $htmlOptions other attributes of the img tag in attribute => value format
     * @return string HTML img tag
     */
    public function typeW($sizes, $widths, $htmlOptions = [])
    {
        $srcSet = [];
        $maxWidth = $this->info[0];

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
                $srcSet[] = preg_replace('#^(.*?)\.(jpe?g|png|gif)$#', "$1@{$width}x-.$2 {$width}w", $this->url);
        $htmlOptions['srcset'] = implode(', ', $srcSet);
        $htmlOptions['sizes'] = $sizes;

        $attrString = self::generateAttributes($htmlOptions);

        return "<img src=\"$this->url\" alt=\"$this->alt\" $attrString>";
    }

    /** Generate img tag with parameter srcset (type x)
     *
     * @param string $maxWidth Maximum width of image
     * @param string $maxHeight Maximum height of image
     * @param $htmlOptions array Other attributes of the img tag in attribute => value format
     * @return string HTML img tag
     */
    public function typeX($maxWidth = '-', $maxHeight = '-', $htmlOptions = [])
    {
        list($src, $htmlOptions['srcset']) = self::srcSetX($maxWidth, $maxHeight);
        $attrString = $this->generateAttributes($htmlOptions);

        return "<img src=\"$src\" alt=\"$this->alt\" $attrString>";
    }

    /** Generate srcset attribute (type x) from parameters
     * @param string $maxWidth Maximum width of image
     * @param string $maxHeight Maximum height of image
     * @return array
     */
    public function srcSetX($maxWidth = '-', $maxHeight = '-')
    {
        $srcSet = [];
        $src = $this->url;
        if ($maxWidth != '-' OR $maxHeight != '-') {
            $src = preg_replace('#^(.*?)\.(jpe?g|png|gif)$#i', "$1@{$maxWidth}x$maxHeight.$2", $this->url);
            $width = $maxWidth;
            $height = $maxHeight;
            foreach ([1, 1.5, 2, 3] as $multiple) {
                if (is_numeric($maxWidth))
                    $width = round($maxWidth * $multiple);

                if (is_numeric($maxHeight))
                    $height = round($maxHeight * $multiple);

                if ($width <= $this->info[0] AND $height <= $this->info[1])
                    $srcSet[] = preg_replace('#^(.*?)\.(jpe?g|png|gif)$#i', "$1@{$width}x$height.$2 {$multiple}x", $this->url);
            }
        }
        return [$src, implode(', ', $srcSet)];
    }

    private function generateAttributes($htmlOptions)
    {
        $attrString = '';
        foreach ($htmlOptions as $attr => $value) {
            if (!empty($attrString))
                $attrString .= ' ';
            $attrString .= "$attr=\"$value\"";
        }
        return $attrString;
    }
}