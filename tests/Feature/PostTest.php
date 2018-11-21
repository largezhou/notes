<?php

namespace Tests\Feature;

use App\Models\Model;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class PostTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    protected function setUp()
    {
        parent::setUp();

        if ($this->getName() == 'testCreatePost') {
            return;
        }

        echo $this->getName() . PHP_EOL;

        $this->prepareBooks();
        $this->prepareNotes();
    }

    public function testGetPosts()
    {
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


    public function testGetPost()
    {
        // 未登录
        // 软删除的
        $this->getResource('posts', 101)
            ->assertStatus(404);
        // 隐藏的
        $this->getResource('posts', 102)
            ->assertStatus(404);

        $this->login();
        Model::clearBootedModels();

        // 软删除的
        $this->getResource('posts', 101)
            ->assertStatus(404);
        // 隐藏的
        $this->getResource('posts', 102)
            ->assertStatus(200);
        // 编辑模式 软删除的
        $this->getResource('posts', 101, [], true)
            ->assertStatus(200);
    }

    public function testCreatePost()
    {
        $this->postCreateResource('posts')
            ->assertStatus(401);

        $this->login();
        Model::clearBootedModels();

        $postData = collect(make(Post::class)->toArray())->except(['created_at', 'updated_at'])->toArray();

        // 正常创建成功
        $input = $postData;
        $this->postCreateResource('posts', $input)
            ->assertStatus(201)
            ->assertJsonFragment(['id' => 1]);
        $input['id'] = 1;
        $this->assertDatabaseHas('notes', $input);

        Post::truncate();

        // 测试不填 desc
        $input = $postData;
        unset($input['desc']);
        $this->postCreateResource('posts', $input)
            ->assertStatus(201)
            ->assertJsonFragment(['id' => 1]);
        $input['id'] = 1;
        $input['desc'] = get_desc($input['html_content'], 100);
        $this->assertDatabaseHas('notes', $input);
    }
}
