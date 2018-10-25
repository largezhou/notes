<?php

namespace Tests\Unit;

use App\Models\Book;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    public function testAddBookManualSetHiddenToTrue()
    {
        $book = Book::addBook(make(Book::class, ['hidden' => true])->toArray());

        $this->assertFalse($book->hidden);

        $this->assertDatabaseHas('books', $book->toArray());
    }
}
