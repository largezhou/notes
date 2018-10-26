<?php

namespace Tests\Unit;

use App\Filters\BookFilter;
use App\Models\Book;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookFilterTest extends TestCase
{
    use DatabaseMigrations;

    public function testRecent()
    {
        create(Book::class, [], 10);

        $request = \Mockery::mock(Request::class, ['only' => ['recent' => null]]);

        $books = Book::filter(new BookFilter($request))->get();

        $this->assertEquals(5, $books->count());
    }
}
