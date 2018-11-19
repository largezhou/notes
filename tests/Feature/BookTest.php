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
use Tests\Traits\BookActions;

class BookTest extends TestCase
{
    use DatabaseMigrations;
    use BookActions;

    public function testPostCreateBook()
    {
        // 未登录
        $this->postCreateBook()
            ->assertStatus(401);

        Model::clearBootedModels();
        $this->login();

        $this->seeErrorText($this->postCreateBook())
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
        unset($input['read']);
        $res = $this->postCreateBook($input);
        $res->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'id'   => 1,
            'read' => 0,
        ]);

        // 已读大于总页数
        $input = $book;
        $input['read'] = $input['total'] + 1;
        $this->seeErrorText($this->postCreateBook($input), '不能大于' . $input['total']);

        // 日期格式不对
        $input = $book;
        $input['started_at'] = 'not a date';
        $this->seeErrorText($this->postCreateBook($input), '格式不对呀');

        // 封面不是图片文件
        $input = $book;
        $input['cover'] = 'not a file';
        $this->seeErrorText($this->postCreateBook($input), '不是图片不行的');

        // 是否隐藏数据是否是 boolean 值
        $input = $book;
        $input['hidden'] = 'not a boolean';
        $this->seeErrorText($this->postCreateBook($input), '只能是是还是不是');

        // 最终创建成功，即使填了 deleted_at 也不会设置
        $input = $book;
        $res = $this->postCreateBook($input);
        $res->assertStatus(201)
            ->assertSee('id');

        $seeData = $input;
        $seeData['cover'] = '/uploads/' . md5_file($input['cover']) . '.jpg';
        $seeData['deleted_at'] = null;
        $seeData['read'] = 0;
        $this->assertDatabaseHas('books', $seeData);
    }

    public function testGetBooks()
    {
        $this->prepareBooks();

        // 未登录的情况，软删除和隐藏的看不到，即使开启了编辑模式
        $this->getBooks(['edit_mode' => 1])
            ->assertStatus(200)
            ->assertJsonCount(8);

        $this->login();
        Model::clearBootedModels();

        $this->getBooks()
            ->assertStatus(200)
            ->assertJsonCount(9)
            ->assertSee('hidden')
            ->assertSee('deleted_at');

        $this->getBooks([], true)
            ->assertJsonCount(10);
    }

    public function testDestroyBook()
    {
        $this->prepareBooks();

        // 软删除和隐藏的 404
        $this->destroyBook(1, true)->assertStatus(404);
        $this->destroyBook(2, true)->assertStatus(404);

        // 正常的，401
        $this->destroyBook(3, true)->assertStatus(401);

        $this->login();
        Model::clearBootedModels();

        // 已经软删除的在非编辑模式下无法查询到
        $this->destroyBook(1)->assertStatus(404);
        // 编辑模式查询到也只是软删除
        $this->destroyBook(1, true)->assertStatus(204);
        $this->assertDatabaseHas('books', [
            'id'         => 1,
            'deleted_at' => Book::find(1)->deleted_at,
        ]);

        // 正常软删除
        $this->destroyBook(2)
            ->assertStatus(204);
        $this->assertDatabaseHas('books', [
            'id'         => 2,
            'deleted_at' => Carbon::now(),
        ]);
    }

    public function testForceDestroyBook()
    {
        $this->prepareBooks();

        $this->forceDestroyBook(1, true)->assertStatus(404);
        $this->forceDestroyBook(2, true)->assertStatus(404);
        $this->forceDestroyBook(3, true)->assertStatus(404);


        Model::clearBootedModels();
        $this->login();

        Book::find(1)->notes()->save(make(Note::class));
        Note::find(1)->tags()->save(make(Tag::class));

        // 没有被软删除的不能彻底删除
        $this->forceDestroyBook(2)->assertStatus(404);
        $this->forceDestroyBook(3)->assertStatus(404);

        // 已经软删除的，可以彻底删除
        $this->forceDestroyBook(1)
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

    public function testShowBook()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        $this->getBook(1, [], true)->assertStatus(404);
        $this->getBook(2, [], true)->assertStatus(404);
        $this->getBook(3, [], true)
            ->assertStatus(200)
            ->assertDontSee('hidden')
            ->assertDontSee('deleted_at')
            ->assertJsonFragment(['notes_count' => 8])
            ->assertJsonCount(8, 'notes');

        // 登录的情况下
        Model::clearBootedModels();
        $this->login();

        // 软删除的
        $this->getBook(1)->assertStatus(404);
        $this->getBook(1, [], true)->assertStatus(200);

        // 隐藏的
        $this->getBook(2)
            ->assertJsonFragment(['hidden' => '1'])
            ->assertJsonFragment(['deleted_at' => null])
            ->assertJsonFragment(['notes_count' => 9])
            ->assertJsonCount(9, 'notes');

        // 笔记编辑模式显示所有
        $this->getBook(2, [], true)
            ->assertJsonFragment(['notes_count' => 10])
            ->assertJsonCount(10, 'notes');
    }

    public function testBookNotesSort()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        // 默认笔记所属页数倒序
        $this->assertOrderBy($this->getBook(4), 'page', 'desc', 'notes');

        $this->assertOrderBy($this->getBook(4, [
            '_sort_field' => 'page',
            '_sort_type'  => 'asc',
        ]), 'page', 'asc', 'notes');

        $this->assertOrderBy($this->getBook(4, [
            '_sort_field' => 'created_at',
            '_sort_type'  => 'asc',
        ]), 'created_at', 'dsc', 'notes');

        $this->assertOrderBy($this->getBook(4, [
            '_sort_field' => 'created_at',
            '_sort_type'  => 'desc',
        ]), 'created_at', 'desc', 'notes');
    }

    public function testUpdateHidden()
    {
        $this->login();

        $this->prepareBooks();

        // 显示
        $this->updateBook(2, ['hidden' => false]);
        $this->assertDatabaseHas((new Book())->getTable(), ['id' => 2, 'hidden' => 0]);

        // 隐藏
        $this->updateBook(2, ['hidden' => true]);
        $this->assertDatabaseHas((new Book())->getTable(), ['id' => 2, 'hidden' => 1]);
    }

    public function testRestoreBook()
    {
        $this->login();

        $this->prepareBooks();

        $this->updateBook(1, ['deleted_at' => null]);
        $this->assertDatabaseHas((new Book())->getTable(), ['id' => 1, 'deleted_at' => null]);
    }

    public function testUpdateBook()
    {
        $this->login();
        $this->prepareBooks();

        $this->updateBook(1, [
            'title' => 'update title',
            'total' => '900',
            'read'  => '666',
        ])->assertStatus(200);

        $this->assertDatabaseHas((new Book())->getTable(), [
            'title' => 'update title',
            'total' => '900',
            'read'  => '666',
        ]);
    }

    public function testUpdateReadOrTotalOnly()
    {
        $this->login();
        $this->prepareBooks();


        $book = Book::editMode()->first();

        $res = $this->updateBook(1, ['read' => '10000']);
        $res->assertStatus(422)
            ->assertSee(json_encode(['read' => ['已读不能大于' . $book->total]]));

        $res = $this->updateBook(1, ['total' => '1']);
        $res->assertStatus(422)
            ->assertSee(json_encode(['total' => ['总页数不能小于' . $book->read]]));
    }
}
