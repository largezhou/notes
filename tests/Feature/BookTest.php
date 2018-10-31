<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
}
