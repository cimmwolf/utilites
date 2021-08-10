<?php

use DenisBeliaev\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testBuild()
    {
        $output = Router::display(__DIR__ . '/fixtures/html/template.html');

        $this->assertStringContainsString('@200x-.png', $output);
        $this->assertStringContainsString('@210x-.png', $output);

        $this->assertStringContainsString('<lazy-img', $output);
    }
}
