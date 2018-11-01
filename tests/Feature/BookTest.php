<?php

namespace Tests\Feature;

use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 准备10个书籍数据，并有一个隐藏的和一个软删除的
     */
    protected function prepareData()
    {
        create(Book::class, [], 10);
        Book::find(1)->delete();
        Book::find(2)->update(['hidden' => true]);
    }

    public function testGuestGetBooks()
    {
        $this->prepareData();

        $res = $this->json('get', route('books.index'));

        $res->assertStatus(200)
            ->assertJsonCount(8)
            ->assertDontSee('hidden')
            ->assertDontSee('deleted_at');
    }

    public function testAuthGetBooks()
    {
        $this->login();

        $this->prepareData();

        $res = $this->json('get', route('books.index'));

        $res->assertStatus(200)
            ->assertJsonCount(9)
            ->assertSee('hidden')
            ->assertSee('deleted_at');

        $res = $this->json('get', route('books.index', ['edit_mode' => 1]));

        $res->assertJsonCount(10);
    }

    protected function postCreateBook($book = [])
    {
        return $this->json('post', route('books.store'), $book);
    }

    public function testAddBook()
    {
        $this->postCreateBook()
            ->assertStatus(401);

        $this->login();

        $this->postCreateBook()
            ->assertStatus(422)
            ->assertSee('title')
            ->assertSee('total')
            ->assertSee('cover');

        $cover = UploadedFile::fake()->image('cover.jpg');
        $book = collect(make(Book::class, ['cover' => $cover, 'hidden' => 1, 'deleted_at' => Carbon::now()]))
            ->only(['title', 'total', 'read', 'started_at', 'cover', 'hidden', 'deleted_at'])
            ->toArray();

        $input = $book;
        $input['read'] = $input['total'] + 1;
        $this->postCreateBook($input)
            ->assertStatus(422)
            ->assertSee(json_encode('已读不能大于'.$input['total']));

        $input = $book;
        $input['cover'] = 'not a file';
        $this->postCreateBook($input)
            ->assertStatus(422)
            ->assertSee(json_encode('封面不是图片不行的'));

        $this->postCreateBook($book)
            ->assertStatus(201)
            ->assertSee('id');

        $this->assertDatabaseHas((new Book)->getTable(), ['id' => 1, 'hidden' => 0, 'deleted_at' => null]);
    }

    protected function destroyBook($id = null)
    {
        return $this->json('delete', route('books.destroy', ['book' => $id ?: 1]));
    }

    public function testDestroyBook()
    {
        $this->destroyBook()
            ->assertStatus(404);

        $this->prepareData();
        $this->destroyBook(3)
            ->assertStatus(401);
    }

    public function testAuthDestroyBook()
    {
        $this->login();

        $this->prepareData();

        // 已经软删除的无法查询到
        $this->destroyBook(1)
            ->assertStatus(404);

        // 正常软删除
        $this->destroyBook(2)
            ->assertStatus(204);

        $this->assertDatabaseHas((new Book())->getTable(), ['id' => 2, 'deleted_at' => Carbon::now()]);
    }

    protected function forceDestroyBook($id = null)
    {
        return $this->json('delete', route('books.force_destroy', ['id' => $id ?: 1]));
    }

    public function testForceDestroyBook()
    {
        $this->prepareData();
        $this->forceDestroyBook()
            ->assertStatus(401);
    }

    public function testAuthForceDestroyBook()
    {
        $this->login();

        $this->prepareData();

        $this->forceDestroyBook(1)
            ->assertStatus(204);

        $this->assertDatabaseMissing((new Book())->getTable(), ['id' => 1]);

        // 没有被软删除的不能彻底删除
        $this->forceDestroyBook(2)
            ->assertStatus(404);
    }

    protected function getBook($id = null, $params = [])
    {
        return $this->json('get', route('books.show', ['book' => $id ?: 1]), $params);
    }

    public function testShowBook()
    {
        // 未登录的情况下

        $this->prepareData();

        // 不存在的书
        $this->getBook(11)
            ->assertStatus(404);

        // 软删除的
        $this->getBook(1)
            ->assertStatus(404);

        // 隐藏的
        $this->getBook(2)
            ->assertStatus(404);
    }

    public function testAuthShowBook()
    {
        // 登录的情况下
        $this->login();

        $this->prepareData();

        // 软删除的
        $this->getBook(1)
            ->assertStatus(404);

        // 隐藏的
        $this->getBook(2)
            ->assertJson(['hidden' => 1]);
    }
}
