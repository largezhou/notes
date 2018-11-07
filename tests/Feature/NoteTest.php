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
            ->assertJsonFragment(['total' => 78]);
    }

    public function testAuthGetNotes()
    {
        $this->login();
        $this->prepareNotes();

        // 有一个软删除的书，和一个软删除的笔记，所以总数应该是89
        $res = $this->getNotes();
        $res->assertJsonFragment(['total' => 89])
            ->assertSee('hidden')
            ->assertSee('deleted_at');

        $res = $this->getNotes(['edit_mode' => 1]);
        $res->assertJsonFragment(['total' => 100]);
    }
}
