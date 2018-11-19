<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    public function testGetDesc()
    {
        $html = '<code>alert(1);</code><p>1234<code>alert(2);</code></p><code></code><code>';

        $this->assertEquals('1234', get_desc($html));
        $this->assertEquals('1234', get_desc($html, 999));
        $this->assertEquals('12', get_desc($html, 2));
    }
}
