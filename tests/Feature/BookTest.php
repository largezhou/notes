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
}
