<?php

namespace Tests\Unit;

use App\Models\Book;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CanHideTest extends TestCase
{
    use DatabaseMigrations;

    public function testNoHidden()
    {
        create(Book::class, [], 10);

        $books = Book::all();
        $this->assertEquals(10, $books->count());
    }

    public function testGlobalScopeOnlyShown()
    {
        create(Book::class, [], 10);
        Book::find(1)->update(['hidden' => true]);

        $books = Book::all();
        $this->assertEquals(9, $books->count());

        $books = Book::withoutGlobalScope('onlyShown')->get();
        $this->assertEquals(10, $books->count());
    }
}
