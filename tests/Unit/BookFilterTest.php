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

        mock_request(['all' => ['recent' => null]]);

        $books = Book::filter(app(BookFilter::class))->get();

        $this->assertEquals(5, $books->count());
    }
}
