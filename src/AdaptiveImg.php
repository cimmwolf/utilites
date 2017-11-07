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
    public function __construct($url, $alt, $path = '', $info = [], $tag = 'img')
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->info = $info;
        $this->tag = $tag;

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

    /**
     * @param string $html
     * @param int $width
     * @return bool
     * @throws \Exception
     */
    public static function adapt($html, $width = 0)
    {
        $alt = '';
        $html = trim($html);
        $tag = 'img';
        if (substr($html, 0, 7) == '<source')
            $tag = 'source';

        preg_match_all('/\s([a-z-]+)=[\'"](.*?)[\'"]/ims', $html, $matches);
        $options = array_combine($matches[1], $matches[2]);
        if ($width > 0)
            $options['width'] = $width;

        foreach (['src', 'sizes', 'alt'] as $att) {
            if (isset($options[$att])) {
                ${$att} = $options[$att];
                unset($options[$att]);
            }
        }

        if (!isset($src, $sizes) && !isset($src, $options['width']))
            throw new \Exception('Wrong img tag to adapt', 400);

        $image = new static($src, $alt, '', [], $tag);

        if (!isset($sizes) && isset($options['width']))
            return $image->typeX($options['width'], '-', $options);

        if (isset($sizes))
            return $image->typeW($sizes, self::calcWidths($sizes), $options);

        return false;
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
        list($htmlOptions['src'], $htmlOptions['srcset']) = self::srcSetX($maxWidth, $maxHeight);
        $htmlOptions['alt'] = $this->alt;
        $attrString = $this->generateAttributes($htmlOptions);

        return "<$this->tag $attrString>";
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

                $descriptor = number_format($multiple, 1);
                if ($width <= $this->info[0] AND $height <= $this->info[1])
                    $srcSet[] = preg_replace('#^(.*?)\.(jpe?g|png|gif)$#i', "$1@{$width}x$height.$2 {$descriptor}x", $this->url);
            }
        }
        return [$src, implode(', ', $srcSet)];
    }

    private function generateAttributes($htmlOptions)
    {
        $attrString = '';
        if ($this->tag == 'source') {
            unset($htmlOptions['alt'], $htmlOptions['src'], $htmlOptions['width']);
        }
        foreach ($htmlOptions as $attr => $value) {
            if (!empty($attrString))
                $attrString .= ' ';
            $attrString .= "$attr=\"$value\"";
        }
        return $attrString;
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
        $htmlOptions['src'] = $this->url;
        $htmlOptions['alt'] = $this->alt;
        $htmlOptions['srcset'] = implode(', ', $srcSet);
        $htmlOptions['sizes'] = $sizes;

        $attrString = self::generateAttributes($htmlOptions);

        return "<$this->tag $attrString>";
    }

    public static function calcWidths($strValue)
    {
        $mediaQueries = self::parseMedia($strValue);
        $queries = [];
        $values = [];

        foreach ($mediaQueries as $query) {
            if (isset($query[1])) {
                $queries[] = $query[0];
                $values[] = $query[1];
            } else
                $defaultValue = $query[0];
        }

        $sizes = [320, 375, 480, 568, 667, 768, 1024, 1280, 1366, 1440, 1640, 1920];
        $widths = [];
        if (!empty($defaultValue))
            foreach ($sizes as $size) {
                $value = $defaultValue;

                foreach ($queries as $key => $query) {
                    if (self::matchMedia($query, ['width' => $size])) {
                        $value = $values[$key];
                        break;
                    }
                }
                $value = preg_replace('/px/i', '', $value);
                $value = preg_replace('/([\d|\.]+)vw/i', '($1*' . $size . '/100)', $value);
                $value = preg_replace('/^calc\((.*?)\)$/i', '$1', $value);

                if (!is_numeric($value))
                    $value = ceil(eval('return' . $value . ';'));
                $widths[] = (int)ceil($value);
            }

        $widths = array_unique($widths);
        sort($widths);

        return $widths;
    }

    protected static function parseMedia($str)
    {
        $queries = explode(',', $str);
        foreach ($queries as &$query) {
            $query = trim($query);
            $query = preg_replace('/(.*?\))\s(.*)/i', '$1||$2', $query);
            $query = explode('||', $query);
        }
        return $queries;
    }

    protected static function matchMedia($media, $screen = [])
    {
        if (strpos($media, ',') !== false)
            $mql = explode(',', $media);
        else
            $mql = [$media];

        $mqIndex = count($mql) - 1;
        $mqLength = $mqIndex;
        $mq = null;

        $exprList = null;
        $expr = null;
        $match = true;

        if ($media == '')
            return true;

        do {
            $mq = $mql[$mqLength - $mqIndex];
            $exprListStr = $mq;

            if (strpos($exprListStr, ' and ') !== false)
                $exprList = explode(' and ', $exprListStr);
            else
                $exprList = [$exprListStr];

            $exprIndex = count($exprList) - 1;

            if ($match && $exprIndex >= 0 && $exprListStr !== '') {
                do {
                    preg_match('/^\s*\(\s*(-[a-z]+-)?(min-|max-)?([a-z\-]+)\s*(:?\s*([0-9]+(\.[0-9]+)?|portrait|landscape)(px|em|dppx|dpcm|rem|%|in|cm|mm|ex|pt|pc|\/([0-9]+(\.[0-9]+)?))?)?\s*\)\s*$/', $exprList[$exprIndex], $expr);

                    if (empty($expr) || empty($screen[$expr[3]])) {
                        $match = false;
                        break;
                    }

                    $prefix = $expr[2];
                    $length = $expr[5];
                    $value = $length;
                    $unit = $expr[7];
                    $feature = $screen[$expr[3]];

//                echo 'prefix =' . $prefix . PHP_EOL;
//                echo 'value = ' . $value . PHP_EOL;
//                echo 'unit = ' . $unit . PHP_EOL;
//                echo 'feature = ' . $feature . PHP_EOL;


                    if ($unit && $unit === 'px')
                        $value = $length;

                    // Test for prefix min or max
                    // Test value against feature
                    if ($prefix === 'min-' && $value) {
                        $match = $feature >= $value;
                    } else if ($prefix === 'max-' && $value) {
                        $match = $feature <= $value;
                    } else if ($value) {
                        $match = $feature === $value;
                    } else {
                        $match = !!$feature;
                    }

                    // If 'match' is false, break loop
                    // Continue main loop through query list
                    if (!$match) {
                        break;
                    }
                } while ($exprIndex--);
            }

            // If match is true, break loop
            // Once matched, no need to check other queries
            if ($match) {
                break;
            }
        } while ($mqIndex--);

        return $match;
    }
}