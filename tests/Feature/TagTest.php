<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagTest extends TestCase
{
    use DatabaseMigrations;

    protected function getTags($data = [])
    {
        return $this->json('get', route('tags.index', $data));
    }

    public function testGetTags()
    {
        $tags = create(Tag::class, [], 20);
        create(Note::class, [], 10);

        // 测试获取所有标签
        $this->getTags()->assertStatus(200)->assertJsonCount(20);

        // 测试获取热门标签
        $tags[0]->notes()->attach([1, 2, 3, 4, 5]);
        $tags[1]->notes()->attach([1, 2, 3]);
        $tags[2]->notes()->attach([1, 2, 3, 4, 5, 6, 7]);

        $this->getTags(['scope' => 'hot'])
            ->assertJsonCount(Tag::HOT_COUNT)
            ->assertSeeInOrder([
                json_encode([
                    'id'   => $tags[2]->id,
                    'name' => $tags[2]->name,
                ]),
                json_encode([
                    'id'   => $tags[0]->id,
                    'name' => $tags[0]->name,
                ]),
                json_encode([
                    'id'   => $tags[1]->id,
                    'name' => $tags[1]->name,
                ]),
            ]);

        // 测试搜索标签
        create(Tag::class, ['name' => 'test 1']);
        create(Tag::class, ['name' => '2 test']);
        create(Tag::class, ['name' => '3 test 3']);

        $this->getTags(['q' => ''])
            ->assertJsonCount(23);
        $this->getTags(['q' => 'test'])
            ->assertJsonCount(3);
    }
}
