<?php

declare(strict_types=1);

namespace DenisBeliaev;

use Exception;

class Router
{
    /**
     * Read file and return modified HTML
     * @param $file
     * @return string
     */
    public static function display($file)
    {
        ob_start();
        require $file;
        $content = ob_get_contents();
        ob_end_clean();

        preg_match_all('~<(?:(lazy-)?img|source)[^<>]+srcset=[\'"]{2}[^<>]*>~miu', $content, $matches);
        foreach ($matches[0] as $match) {
            $clearHtml = str_replace('<lazy-img ', '<img ', $match);
            try {
                $adaptiveImg = AdaptiveImg::adapt($clearHtml);
                if ($match != $clearHtml) {
                    $adaptiveImg = str_replace('<img ', '<lazy-img ', $adaptiveImg);
                }
                $content = str_replace($match, $adaptiveImg, $content);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        if (preg_match('~^(.+?)(<body.*?>.+?</body>)(.+)$~iums', $content, $parts)) {
            unset($parts[0]);
            $parts[2] = Typograph::string($parts[2]);
            $content = implode('', $parts);
        }
        return $content;
    }
}
