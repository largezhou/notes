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

    protected function getNotes($params = [])
    {
        return $this->json('get', route('notes.index'), $params);
    }

    public function testGetNotes()
    {
        $this->prepareNotes();

        $res = $this->getNotes();
        $res->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertDontSee('hidden')
            ->assertDontSee('deleted_at')
            ->assertJsonFragment(['total' => 80]);
    }

    public function testAuthGetNotes()
    {
        $this->login();
        $this->prepareNotes();

        $res = $this->getNotes();
        $res->assertJsonFragment(['total' => 90])
            ->assertSee('hidden')
            ->assertSee('deleted_at');

        $res = $this->getNotes(['edit_mode' => 1]);
        $res->assertJsonFragment(['total' => 100]);
    }
}
