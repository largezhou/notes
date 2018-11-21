<?php

namespace Tests\Feature;

use App\Models\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class PostTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    public function testGetPosts()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        $this->getResources('posts')
            ->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertDontSee('hidden')
            ->assertDontSee('deleted_at')
            ->assertJsonFragment(['total' => 18]);

        $this->login();
        Model::clearBootedModels();

        $this->getResources('posts')
            ->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertSee('hidden')
            ->assertSee('deleted_at')
            ->assertJsonFragment(['total' => 19]);

        $this->getResources('posts', [], true)
            ->assertStatus(200)
            ->assertJsonFragment(['total' => 20]);

        // 第二页
        $this->getResources('posts', ['page' => 2], true)
            ->assertJsonCount(5, 'data')
            ->assertJsonFragment(['current_page' => 2]);
    }
}
