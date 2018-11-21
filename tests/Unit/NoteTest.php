<?php

namespace Tests\Unit;

use App\Models\Note;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NoteTest extends TestCase
{
    use DatabaseMigrations;

    public function testNotesOnlyHasNotes()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        $this->assertEquals(100, Note::showAll()->count());
        $this->assertEquals(110, Note::withoutGlobalScope('notes')->showAll()->count());
    }

    public function testPostsOnlyHasPosts()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        $this->assertEquals(10, Post::showAll()->count());
        $this->assertEquals(110, Post::withoutGlobalScope('posts')->showAll()->count());
    }
}
