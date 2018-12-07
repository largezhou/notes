<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Model;
use App\Models\Note;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class BookTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    public function testPostCreateBook()
    {
        // 未登录
        $this->postCreateResource('books')
            ->assertStatus(401);

        Model::clearBootedModels();
        $this->login();

        $this->seeErrorText($this->postCreateResource('books'))
            ->assertSee('title')
            ->assertSee('total')
            ->assertSee('cover');

        // 准备数据
        $cover = UploadedFile::fake()->image('cover.jpg');
        $book = collect(make(Book::class, ['cover' => $cover, 'hidden' => 1, 'deleted_at' => (string) Carbon::now()]))
            ->only(['title', 'total', 'read', 'started_at', 'cover', 'hidden', 'deleted_at'])
            ->toArray();

        // 不填已读
        $input = $book;
        $input['read'] = null;
        $res = $this->postCreateResource('books', $input);
        $res->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'id'   => 1,
            'read' => 0,
        ]);

        $input = $book;
        unset($input['read']);
        $res = $this->postCreateResource('books', $input);
        $res->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'id'   => 1,
            'read' => 0,
        ]);

        // 已读大于总页数
        $input = $book;
        $input['read'] = $input['total'] + 1;
        $this->seeErrorText($this->postCreateResource('books', $input), '不能大于' . $input['total']);

        // 日期格式不对
        $input = $book;
        $input['started_at'] = 'not a date';
        $this->seeErrorText($this->postCreateResource('books', $input), '格式不对呀');

        // 封面不是图片文件
        $input = $book;
        $input['cover'] = 'not a file';
        $this->seeErrorText($this->postCreateResource('books', $input), '不是图片不行的');

        // 是否隐藏数据是否是 boolean 值
        $input = $book;
        $input['hidden'] = 'not a boolean';
        $this->seeErrorText($this->postCreateResource('books', $input), '只能是是还是不是');

        // 最终创建成功，即使填了 deleted_at 也不会设置
        $input = $book;
        $res = $this->postCreateResource('books', $input);
        $res->assertStatus(201)
            ->assertSee('id');

        $seeData = $input;
        $seeData['cover'] = '/uploads/' . md5_file($input['cover']) . '.jpg';
        $seeData['read'] = 0;
        $this->assertDatabaseHas('books', $seeData);
    }

    public function testGetBooks()
    {
        $this->prepareBooks();

        // 未登录的情况，软删除和隐藏的看不到，即使开启了编辑模式
        $this->getResources('books', ['edit_mode' => 1])
            ->assertStatus(200)
            ->assertJsonCount(8);

        $this->login();
        Model::clearBootedModels();

        $this->getResources('books')
            ->assertStatus(200)
            ->assertJsonCount(9)
            ->assertSee('hidden')
            ->assertSee('deleted_at');

        $this->getResources('books', [], true)
            ->assertJsonCount(10);

        // recent
        $this->getResources('books', ['recent' => 1])
            ->assertJsonCount(Book::VERY_RECENT_COUNT);
    }

    public function testDestroyBook()
    {
        $this->prepareBooks();

        // 软删除和隐藏的 404
        $this->destroyResource('books', 1, true)->assertStatus(404);
        $this->destroyResource('books', 2, true)->assertStatus(404);

        // 正常的，401
        $this->destroyResource('books', 3, true)->assertStatus(401);

        $this->login();
        Model::clearBootedModels();

        // 已经软删除的在非编辑模式下无法查询到
        $this->destroyResource('books', 1)->assertStatus(404);
        // 编辑模式查询到也只是软删除
        $this->destroyResource('books', 1, true)->assertStatus(204);
        $this->assertDatabaseHas('books', [
            'id'         => 1,
            'deleted_at' => Book::find(1)->deleted_at,
        ]);

        // 正常软删除
        $deletedAt = (string) Carbon::now();
        $this->destroyResource('books', 2)
            ->assertStatus(204);
        $this->assertDatabaseHas('books', [
            'id'         => 2,
            'deleted_at' => $deletedAt,
        ]);
    }

    public function testForceDestroyBook()
    {
        $this->prepareBooks();

        $this->forceDestroyResource('books', 1, true)->assertStatus(404);
        $this->forceDestroyResource('books', 2, true)->assertStatus(404);
        $this->forceDestroyResource('books', 3, true)->assertStatus(404);


        Model::clearBootedModels();
        $this->login();

        Book::find(1)->notes()->save(make(Note::class));
        Note::find(1)->tags()->save(make(Tag::class));

        // 没有被软删除的不能彻底删除
        $this->forceDestroyResource('books', 2)->assertStatus(404);
        $this->forceDestroyResource('books', 3)->assertStatus(404);

        // 已经软删除的，可以彻底删除
        $this->forceDestroyResource('books', 1)
            ->assertStatus(204);

        // 删除后，笔记、笔记和标签的关联一并删除
        $this->assertDatabaseMissing('books', ['id' => 1]);
        $this->assertDatabaseMissing('notes', ['id' => 1]);
        $this->assertDatabaseMissing('model_tags', [
            'tag_id'      => 1,
            'target_id'   => 1,
            'target_type' => 'notes',
        ]);
    }

    public function testGetBook()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        $this->getResource('books', 1, [], true)->assertStatus(404);
        $this->getResource('books', 2, [], true)->assertStatus(404);
        $this->getResource('books', 3, [], true)
            ->assertStatus(200)
            ->assertDontSee('hidden')
            ->assertDontSee('deleted_at')
            ->assertJsonFragment(['notes_count' => 8])
            ->assertJsonCount(8, 'notes');

        // 登录的情况下
        Model::clearBootedModels();
        $this->login();

        // 软删除的
        $this->getResource('books', 1)->assertStatus(404);
        $this->getResource('books', 1, [], true)->assertStatus(200);

        // 隐藏的
        $this->getResource('books', 2)
            ->assertJsonFragment(['hidden' => '1'])
            ->assertJsonFragment(['deleted_at' => null])
            ->assertJsonFragment(['notes_count' => 9])
            ->assertJsonCount(9, 'notes');

        // 笔记编辑模式显示所有
        $this->getResource('books', 2, [], true)
            ->assertJsonFragment(['notes_count' => 10])
            ->assertJsonCount(10, 'notes');
    }

    public function testBookNotesSort()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        // 默认笔记所属页数倒序
        $this->assertOrderBy($this->getResource('books', 4), 'page', 'desc', 'notes');
        // 不提供排序类型，则忽略
        $this->assertOrderBy($this->getResource('books', 4, ['_sort_field' => 'created_at']), 'page', 'desc', 'notes');

        $this->assertOrderBy($this->getResource('books', 4, [
            '_sort_field' => 'page',
            '_sort_type'  => 'asc',
        ]), 'page', 'asc', 'notes');

        $this->assertOrderBy($this->getResource('books', 4, [
            '_sort_field' => 'created_at',
            '_sort_type'  => 'asc',
        ]), 'created_at', 'dsc', 'notes');

        $this->assertOrderBy($this->getResource('books', 4, [
            '_sort_field' => 'created_at',
            '_sort_type'  => 'desc',
        ]), 'created_at', 'desc', 'notes');
    }

    public function testUpdateHidden()
    {
        $this->prepareBooks();

        $this->updateResource('books', 1)->assertStatus(404);
        $this->updateResource('books', 2)->assertStatus(404);
        $this->updateResource('books', 3)->assertStatus(401);

        Model::clearBootedModels();
        $this->login();

        // 显示
        $this->updateResource('books', 2, ['hidden' => false])->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => 2, 'hidden' => 0]);

        // 隐藏
        $this->updateResource('books', 2, ['hidden' => true])->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => 2, 'hidden' => 1]);
    }

    public function testRestoreBook()
    {
        $this->login();
        $this->prepareBooks();

        $this->updateResource('books', 1, ['deleted_at' => null])->assertStatus(404);

        $this->updateResource('books', 1, ['deleted_at' => null], true)->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => 1, 'deleted_at' => null]);
    }

    public function testUpdateBook()
    {
        $this->login();
        $this->prepareBooks();

        // 啥都不更新
        $this->updateResource('books', 3)->assertStatus(200);

        $book = Book::find(3)->toArray();
        $book['cover'] = asset($book['cover']);
        unset($book['updated_at']);

        // 啥都不更新
        $this->updateResource('books', 3, $book)->assertStatus(200);

        $this->assertDatabaseHas('books', Book::find(3)->toArray());

        // 更新错误的封面
        $input = $book;
        $input['cover'] = 'not a cover';
        $this->seeErrorText($this->updateResource('books', 3, $input), '不是图片不行的');

        // 更新正确的封面
        $input['cover'] = UploadedFile::fake()->image('cover.jpg');
        $this->updateResource('books', 3, $input)->assertStatus(200);
        $input['cover'] = '/uploads/' . md5_file($input['cover']) . '.jpg';
        $this->assertDatabaseHas('books', $input);

        $book['cover'] = asset($input['cover']);

        // 去掉 started_at
        $input = $book;
        $input['started_at'] = null;
        $this->updateResource('books', 3, $input)->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => 3, 'started_at' => null]);
    }

    public function testUpdateReadOrTotal()
    {
        $this->login();
        $this->prepareBooks();

        $book = Book::find(3);

        // 只更新 read
        $this->updateResource('books', 3, ['read' => 'not a numeric'])
            ->assertStatus(422)
            ->assertSee(json_encode(['read' => ['已读要一个整数']]))
            ->assertDontSee('total');

        $this->updateResource('books', 3, ['read' => '10000'])
            ->assertStatus(422)
            ->assertSee(json_encode(['read' => ['已读不能大于' . $book->total]]))
            ->assertDontSee('total');

        // 只更新 total
        $this->updateResource('books', 3, ['total' => 'not a numeric'])
            ->assertStatus(422)
            ->assertSee(json_encode(['total' => ['总页数要一个整数']]))
            ->assertDontSee('read');

        $this->updateResource('books', 3, ['total' => '1'])
            ->assertStatus(422)
            ->assertSee(json_encode(['total' => ['总页数不能小于' . $book->read]]))
            ->assertDontSee('read');

        // read 和 total 同时更新
        $this->updateResource('books', 3, ['read' => 999, 'total' => 1])
            ->assertStatus(422)
            ->assertJsonFragment(['read' => ['已读不能大于1']]);

        $this->updateResource('books', 3, ['read' => 50, 'total' => 100])
            ->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => 3, 'read' => 50, 'total' => 100]);
    }
}
