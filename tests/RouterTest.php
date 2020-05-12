<?php

use DenisBeliaev\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testBuild()
    {
        $output = Router::display(__DIR__ . '/fixtures/html/template.html');

        $this->assertContains('@200x-.png', $output);
        $this->assertContains('@210x-.png', $output);

        $this->assertContains('<lazy-img', $output);
    }
}
