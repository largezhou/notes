<?php

namespace Tests\Feature;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NoteTest extends TestCase
{
    use DatabaseMigrations;

    protected function prepareNotes()
    {
        (new \BookTableSeeder())->run();
        $notes = Note::latest()->with('book')->get();

        $firstBook = $notes->first()->book;
        $firstBook->update(['hidden' => true]);

        $secondBook = $notes[1]->book;
        $secondBook->delete();
    }

    public function testGetNotes()
    {
        $this->prepareNotes();

        $res = $this->json('get', route('notes.index'));
        $res->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonFragment(['total' => 80]);
    }
}
