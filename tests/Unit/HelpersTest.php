<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    public function testGetDesc()
    {
        $html = '<pre><code>alert(1);</code></pre><p>1中2文3啊4<code>alert(2);</code></p><code></code><code>';

        $this->assertEquals('1中2文3啊4alert(2);', get_desc($html));
        $this->assertEquals('1中2文3啊4alert(2);', get_desc($html, 999));
        $this->assertEquals('1中', get_desc($html, 2));
    }
}
