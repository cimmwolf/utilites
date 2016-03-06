<?php
/**
 * Author: Denis Beliaev <cimmwolf@gmail.com>
 */

namespace DenisBeliaev;

class Typograph
{
    /**
     * @param string $path Absolute path to filename
     * @param array $settings Settings for emuravjev/mdash
     * @return string
     */
    static function file($path, $settings = []) {
        ob_start();
        include $path;
        $content = ob_get_contents();
        ob_end_clean();

        return self::string($content, $settings);
    }

    /**
     * @param string $content Content for typograph
     * @param array $settings  Settings for emuravjev/mdash
     * @return string
     */
    static function string($content, $settings = [])
    {
        $settings = array_merge($settings, [
            'Text.auto_links' => 'off',
            'Text.email' => 'off',
            'Text.paragraphs' => 'off',
            'Text.breakline' => 'off',
        ]);


        $T = new \EMTypograph();
        $T->setup($settings);
        $T->set_text($content);

        return $T->apply();
    }
}