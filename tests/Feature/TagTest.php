<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Note;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class TagTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    public function testGetTags()
    {
        $tags = create(Tag::class, [], 20);
        create(Note::class, ['book_id' => create(Book::class)->id], 10);

        // 测试获取所有标签
        $this->getResources('tags')->assertStatus(200)->assertJsonCount(20);

        // 测试获取热门标签
        $tags[0]->notes()->attach([1, 2, 3, 4, 5]);
        $tags[1]->notes()->attach([1, 2, 3]);
        $tags[2]->notes()->attach([1, 2, 3, 4, 5, 6, 7]);
        $tags[2]->posts()->attach([create(Post::class)->id]);

        $res = $this->getResources('tags', ['scope' => 'hot']);
        $res->assertJsonCount(Tag::HOT_COUNT)
            ->assertSeeInOrder([
                json_encode([
                    'id' => $tags[2]->id,
                    'name' => $tags[2]->name,
                    'notes_count' => '7',
                    'posts_count' => '1',
                ]),
                json_encode([
                    'id' => $tags[0]->id,
                    'name' => $tags[0]->name,
                    'notes_count' => '5',
                    'posts_count' => '0',
                ]),
                json_encode([
                    'id' => $tags[1]->id,
                    'name' => $tags[1]->name,
                    'notes_count' => '3',
                    'posts_count' => '0',
                ]),
            ], false);

        // 测试搜索标签
        create(Tag::class, ['name' => 'test 1']);
        create(Tag::class, ['name' => '2 test']);
        create(Tag::class, ['name' => '3 test 3']);

        $this->getResources('tags', ['q' => ''])
            ->assertJsonCount(23);
        $this->getResources('tags', ['q' => 'test'])
            ->assertJsonCount(3);
    }

    public function testDestroyTag()
    {
        $note = create(Note::class);
        $tag = create(Tag::class);
        $note->tags()->attach($tag->id);

        $this->destroyResource('tags', 1)->assertStatus(401);

        $this->login();
        $this->destroyResource('tags', 1)->assertStatus(204);

        $this->assertDatabaseMissing('tags', ['id' => 1]);
        $this->assertDatabaseMissing('model_tags', [
            'tag_id' => 1,
            'target_id' => $note->id,
            'target_type' => 'notes',
        ]);
    }

    public function testUpdateTag()
    {
        $this->login();

        create(Tag::class, ['name' => 'tag1']);
        create(Tag::class, ['name' => 'tag2']);

        $res = $this->updateResource('tags', 1, ['name' => 'tag2']);
        $res->assertStatus(422);
        $this->assertJsonContains('千万不能重复啊', $res->getContent());

        $this->updateResource('tags', 1, ['name' => 'tag1'])->assertStatus(204);

        $this->updateResource('tags', 1, ['name' => 'updated'])->assertStatus(204);
        $this->assertDatabaseHas('tags', [
            'id' => 1,
            'name' => 'updated',
        ]);
    }
}
