<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommandsTest extends TestCase
{
    use DatabaseMigrations;

    public function testInstall()
    {
        $this->artisan('notes:install')
            ->expectsQuestion('设置密码 [000000]：', '123456')
            ->expectsQuestion('再次输入密码 [000000]：', '000000')
            ->expectsOutput('两次密码不一致')
            ->expectsQuestion('设置密码 [000000]：', '123456')
            ->expectsQuestion('再次输入密码 [000000]：', '123456')
            ->expectsOutput('123456')
            ->expectsOutput('largezhou 创建成功')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'id'       => 1,
            'username' => 'largezhou',
        ]);
        $this->assertTrue(\Hash::check('123456', User::first()->password));

        $this->artisan('notes:install')
            ->expectsQuestion('设置密码 [000000]：', '000000')
            ->expectsQuestion('再次输入密码 [000000]：', '000000')
            ->expectsOutput('密码修改成功')
            ->assertExitCode(0);

        $this->assertTrue(\Hash::check('000000', User::first()->password));
    }
}
