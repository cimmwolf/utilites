<?php

use DenisBeliaev\AdaptiveImg;

class AdaptiveImgTest extends \PHPUnit\Framework\TestCase
{
    public function testTypeX()
    {
        $expected = '<img src="test@170x-.jpg" alt="" srcset="test@170x-.jpg 1.0x, test@255x-.jpg 1.5x, test@340x-.jpg 2.0x, test@510x-.jpg 3.0x" data-aspect-ratio="0.56">';
        $expected2 = '<img src="test@170x-.jpg" alt="" width="170" srcset="test@170x-.jpg 1.0x, test@255x-.jpg 1.5x, test@340x-.jpg 2.0x, test@510x-.jpg 3.0x" data-aspect-ratio="0.56">';
        $expected3 = '<source srcset="test@170x-.jpg 1.0x, test@255x-.jpg 1.5x, test@340x-.jpg 2.0x, test@510x-.jpg 3.0x" data-aspect-ratio="0.56">';

        $generatedImg = new AdaptiveImg('test.jpg', '');
        $generatedImg = $generatedImg->typeX('170');

        $generatedImg2 = AdaptiveImg::adapt('<img src="test.jpg" width="170">');
        $generatedImg3 = AdaptiveImg::adapt('<img src="test.jpg">', 170);
        $generatedImg4 = AdaptiveImg::adapt('<source src="test.jpg">', 170);

        $this->assertEquals($this->explode($expected), $this->explode($generatedImg));
        $this->assertEquals($this->explode($expected2), $this->explode($generatedImg2));
        $this->assertEquals($this->explode($expected2), $this->explode($generatedImg3));
        $this->assertEquals($this->explode($expected3), $this->explode($generatedImg4));
    }

    private function explode($html)
    {
        preg_match_all('/\s([a-z-]+)=[\'"](.*?)[\'"]/ims', $html, $matches);
        $attributes = array_combine($matches[1], $matches[2]);
        $attributes['_tag'] = preg_replace('/^<(\w+).*/i', '$1', $html);
        return $attributes;
    }

    public function testTypeW()
    {
        $expected = '<img src="test.jpg" alt="" class="class-1 class-2" style="border: 1px solid black" data-name="image" srcset="test@160x-.jpg 160w, test@188x-.jpg 188w, test@240x-.jpg 240w, test@284x-.jpg 284w, test@320x-.jpg 320w, test@334x-.jpg 334w, test@376x-.jpg 376w, test@384x-.jpg 384w, test@480x-.jpg 480w, test@512x-.jpg 512w, test@564x-.jpg 564w, test@568x-.jpg 568w, test@640x-.jpg 640w, test@668x-.jpg 668w, test@720x-.jpg 720w, test@768x-.jpg 768w, test@852x-.jpg 852w, test@960x-.jpg 960w, test@1002x-.jpg 1002w, test@1024x-.jpg 1024w, test@1152x-.jpg 1152w, test@1536x-.jpg 1536w, test@1920x-.jpg 1920w" sizes="(max-width: 992px) calc(50vw), (max-width: 1199px) 512px, (max-width: 1279px) 25vw, 320px" data-aspect-ratio="0.56">';
        $expected2 = '<source srcset="test@160x-.jpg 160w, test@188x-.jpg 188w, test@240x-.jpg 240w, test@284x-.jpg 284w, test@320x-.jpg 320w, test@334x-.jpg 334w, test@376x-.jpg 376w, test@384x-.jpg 384w, test@480x-.jpg 480w, test@512x-.jpg 512w, test@564x-.jpg 564w, test@568x-.jpg 568w, test@640x-.jpg 640w, test@668x-.jpg 668w, test@720x-.jpg 720w, test@768x-.jpg 768w, test@852x-.jpg 852w, test@960x-.jpg 960w, test@1002x-.jpg 1002w, test@1024x-.jpg 1024w, test@1152x-.jpg 1152w, test@1536x-.jpg 1536w, test@1920x-.jpg 1920w" sizes="(max-width: 992px) calc(50vw), (max-width: 1199px) 512px, (max-width: 1279px) 25vw, 320px" data-aspect-ratio="0.56">';

        $generatedImg = AdaptiveImg::adapt('<img 
                src="test.jpg" 
                sizes="(max-width: 992px) calc(50vw), (max-width: 1199px) 512px, (max-width: 1279px) 25vw, 320px"
                class="class-1 class-2"
                style="border: 1px solid black"
                data-name="image">');

        $generatedImg2 = new AdaptiveImg('test.jpg', '');
        $generatedImg2 = $generatedImg2->typeW('(max-width: 992px) calc(50vw), (max-width: 1199px) 512px, (max-width: 1279px) 25vw, 320px', [160, 188, 240, 284, 320, 334, 384, 512], [
            'class'     => 'class-1 class-2',
            'style'     => 'border: 1px solid black',
            'data-name' => 'image']);

        $generatedImg3 = AdaptiveImg::adapt('
            <source src="test.jpg" sizes="(max-width: 992px) calc(50vw), (max-width: 1199px) 512px, (max-width: 1279px) 25vw, 320px">
        ');

        $generatedImg4 = new AdaptiveImg('test.jpg', '');
        $generatedImg4 = $generatedImg4->typeW('(max-width: 992px) calc(50vw), (max-width: 1199px) 512px, (max-width: 1279px) 25vw, 320px', [], [
            'class'     => 'class-1 class-2',
            'style'     => 'border: 1px solid black',
            'data-name' => 'image']);

        $this->assertEquals($this->explode($expected), $this->explode($generatedImg));
        $this->assertEquals($this->explode($expected), $this->explode($generatedImg2));
        $this->assertEquals($this->explode($expected), $this->explode($generatedImg4));

        $this->assertEquals($this->explode($expected2), $this->explode($generatedImg3));
    }
}
