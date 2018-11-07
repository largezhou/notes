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

    protected function getNote($id)
    {
        return $this->json('get', route('notes.show', ['id' => $id]));
    }

    public function testShowNote()
    {
        $this->prepareNotes();

        $books = Book::withHidden()->withTrashed()->get();

        // 软删除的书
        $book1Notes = $books[0]->notes;
        $this->getNote($book1Notes[0]->id)->assertStatus(404);

        // 隐藏的书
        $book2Notes = $books[1]->notes;
        $this->getNote($book2Notes[0]->id)->assertStatus(404);

        // 正常的书的笔记
        $book3Notes = $books[2]->notes()->showAll()->get();
        // 软删除的笔记
        $this->getNote($book3Notes[0]->id)->assertStatus(404);
        // 隐藏的笔记
        $this->getNote($book3Notes[1]->id)->assertStatus(404);
        // 正常笔记
        $this->getNote($book3Notes[2]->id)->assertStatus(200);
    }

    public function testAuthShowNote()
    {
        $this->login();
        $this->prepareNotes();

        $books = Book::withHidden()->withTrashed()->get();

        // 软删除的书
        $book1Notes = $books[0]->notes;
        $this->getNote($book1Notes[0]->id)->assertStatus(404);

        // 隐藏的书
        $book2Notes = $books[1]->notes;
        $this->getNote($book2Notes[0]->id)->assertStatus(200);
    }
}
