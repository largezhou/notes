<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    protected function postLogin($data)
    {
        return $this->json('post', route('login'), $data);
    }

    public function testLogin()
    {
        create(User::class, ['username' => 'largezhou', 'password' => bcrypt('000000')]);

        $this->postLogin([])->assertStatus(422);

        $res = $this->postLogin([
            'username' => 'largezhou1',
            'password' => '000000',
        ]);
        $res->assertStatus(422);

        $res = $this->postLogin([
            'username' => 'largezhou',
            'password' => '0000000',
        ]);
        $res->assertStatus(422);

        $res = $this->postLogin([
            'username' => 'largezhou',
            'password' => '000000',
        ]);
        $res->assertStatus(200)->assertSee('token')->assertSee('expires_in');
    }

    public function testLogout()
    {
        $this->login();
        $this->json('post', route('logout'), ['token' => $this->token])->assertStatus(204);
        $this->json('post', route('logout'))->assertStatus(401);
    }

    public function testGetInfo()
    {
        $this->json('get', route('info'))->assertStatus(401);
        $this->login();
        $this->json('get', route('info'))->assertStatus(200)->assertJson(['name' => $this->user->username]);
    }
}
