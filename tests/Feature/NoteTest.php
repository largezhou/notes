<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Note;
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

    protected function createNote($bookId, $data = [])
    {
        return $this->json('post', route('notes.store', ['book' => $bookId]), $data);
    }

    public function testCreateNote()
    {
        $this->prepareBook();

        $this->createNote(3)->assertStatus(401);

        $this->login();

        $this->createNote(999)->assertStatus(404);

        $book = Book::find(3);
        $noteData = make(Note::class, ['hidden' => '1', 'page' => $book->total - 1])->toArray();
        $noteData = array_except($noteData, ['book_id']);

        $testData = $noteData;
        $testData['page'] = $book->total + 1;
        $res = $this->createNote(3, $testData);
        $res->assertStatus(422);
        $this->assertJsonContains("不能超过{$book->total}页", $res->getContent());

        $testData = $noteData;
        $testData['tags'] = 'string';
        $res = $this->createNote(3, $testData);
        $this->assertJsonContains('数据格式不对', $res->getContent());

        $res = $this->createNote(3, $noteData);
        $res->assertStatus(201)
            ->assertJson(['id' => 1]);

        $testData = array_except($noteData, ['created_at', 'updated_at']);
        $testData['book_id'] = $book->id;
        $this->assertDatabaseHas((new Note())->getTable(), $testData);
    }

    protected function destroyNote($id)
    {
        return $this->json('delete', route('notes.destroy', ['note' => $id]));
    }

    public function testDestroyNote()
    {
        create(Note::class);

        $this->destroyNote(1)->assertStatus(401);

        $this->login();

        $this->destroyNote(1)->assertStatus(204);
    }
}
