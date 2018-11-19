<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Model;
use App\Models\Note;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class NoteTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    public function testGetNotes()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        // 没有登录的情况下，即使是 编辑模式，也不能看到软删除的和隐藏的
        // 有 1 本书隐藏，1 本书软删除，每本书的 10 条笔记中，都有 1 条软删除的和隐藏的
        // 所以总数是 64 条
        $this->getResources('notes', [], true)
            ->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertDontSee('hidden')
            ->assertDontSee('deleted_at')
            ->assertJsonFragment(['total' => 64]);

        $this->login();
        Model::clearBootedModels();

        // 登录后可以看到隐藏的，总数为：9 * 9
        $this->getResources('notes')
            ->assertJsonFragment(['total' => 81])
            ->assertSee('hidden')
            ->assertSee('deleted_at');

        // 编辑模式可以看到所有
        $this->getResources('notes', [], true)
            ->assertJsonFragment(['total' => 100]);
    }

    public function testGetNote()
    {
        $this->prepareBooks();
        $this->prepareNotes();

        $books = Book::showAll()->get();

        // 软删除的书
        $softDeletedBookNotes = $books[0]->notes;
        $this->getResource('notes', $softDeletedBookNotes[2]->id)->assertStatus(404);

        // 隐藏的书
        $hiddenBookNotes = $books[1]->notes;
        $this->getResource('notes', $hiddenBookNotes[2]->id)->assertStatus(404);

        // 正常的书的笔记
        $bookNotes = $books[2]->notes()->showAll()->get();
        // 软删除的笔记
        $this->getResource('notes', $bookNotes[0]->id)->assertStatus(404);
        // 隐藏的笔记
        $this->getResource('notes', $bookNotes[1]->id)->assertStatus(404);
        // 正常笔记
        $this->getResource('notes', $bookNotes[2]->id)->assertStatus(200);

        $this->login();
        Model::clearBootedModels();

        // 软删除的书的笔记
        $this->getResource('notes', $softDeletedBookNotes[2]->id)->assertStatus(404);
        // 隐藏的书的笔记
        $this->getResource('notes', $hiddenBookNotes[2]->id)->assertStatus(200);
        // 编辑模式下 软删除的书的笔记
        $this->getResource('notes', $softDeletedBookNotes[2]->id, [], true)->assertStatus(200);
        // 软删除的笔记
        $this->getResource('notes', $bookNotes[0]->id)->assertStatus(404);
        // 隐藏的笔记
        $this->getResource('notes', $bookNotes[1]->id)->assertStatus(200);
        // 编辑模式下 软删除的笔记
        $this->getResource('notes', $bookNotes[0]->id, [], true)->assertStatus(200);
    }

    protected function createNote($bookId, $data = [])
    {
        return $this->json('post', route('notes.store', ['book' => $bookId]), $data);
    }

    public function testCreateNote()
    {
        $this->prepareBooks();

        $this->createNote(3)->assertStatus(401);

        $this->login();

        $this->createNote(999)->assertStatus(404);

        $book = Book::find(3);
        // 把笔记的所属页数，设置为书籍已读页多一点，避免与书籍的 read 数相同，之后用来测试标记为读到此页
        $noteData = make(Note::class, ['hidden' => '1', 'page' => $book->read + 5])->toArray();
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

        // 创建成功，没有标记为读到此页
        $res = $this->createNote(3, $noteData);
        $res->assertStatus(201)
            ->assertJson(['id' => 1]);

        $testData = array_except($noteData, ['created_at', 'updated_at']);
        $testData['book_id'] = $book->id;
        $this->assertDatabaseHas((new Note())->getTable(), $testData);
        $this->assertDatabaseHas((new Book())->getTable(), ['id' => $book->id, 'read' => $book->read]);

        // 标记为读到此页
        $res = $this->createNote(3, $noteData + ['mark_read' => true]);
        $testData['book_id'] = $book->id;
        $this->assertDatabaseHas((new Note())->getTable(), $testData);
        $this->assertDatabaseHas((new Book())->getTable(), ['id' => $book->id, 'read' => $testData['page']]);
    }

    public function testCreateNoteWithTags()
    {
        $this->login();

        $book = create(Book::class);


        $existsTag = create(Tag::class)->toArray();
        $newTag = 'test tag';
        $noteData = make(Note::class, [
            'tags' => [$existsTag, $newTag],
            'page' => $book->total - 1,
        ])->toArray();

        $res = $this->createNote(1, $noteData);
        $res->assertStatus(201);

        $this->assertDatabaseHas((new Tag())->getTable(), ['name' => $newTag]);
        $this->assertDatabaseHas('model_tags', [
            'tag_id'      => $existsTag['id'],
            'target_id'   => 1,
            'target_type' => 'notes',
        ]);

        $this->assertDatabaseHas('model_tags', [
            'tag_id'      => 2,
            'target_id'   => 1,
            'target_type' => 'notes',
        ]);
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

    public function testForceDestroyNote()
    {
        $note = create(Note::class);
        $tag = make(Tag::class);
        $note->tags()->save($tag);

        $forceDestroy = function ($id) {
            return $this->json('delete', route('notes.force_destroy', ['id' => $id]));
        };

        $forceDestroy(1)->assertStatus(401);

        $this->login();

        $forceDestroy(1)->assertStatus(404);

        $this->destroyNote(1);
        $forceDestroy(1)->assertStatus(204);
        $this->assertDatabaseMissing('notes', ['id' => 1]);
        $this->assertDatabaseMissing('model_tags', [
            'target_id'   => 1,
            'target_type' => 'notes',
            'tag_id'      => 1,
        ]);
    }

    protected function updateNote($id, $data = [])
    {
        return $this->json('put', route('notes.update', ['id' => $id]), $data);
    }

    public function testToggleHiddenNote()
    {
        $this->login();
        create(Note::class, ['book_id' => 1]);
        create(Book::class);

        $res = $this->updateNote(1, ['hidden' => true]);
        $res->assertStatus(200);
        $this->assertDatabaseHas((new Note())->getTable(), ['id' => 1, 'hidden' => 1]);
        $this->updateNote(1, ['hidden' => false]);
        $this->assertDatabaseHas((new Note())->getTable(), ['id' => 1, 'hidden' => 0]);
    }

    public function testRestoreNote()
    {
        $this->login();

        create(Note::class, ['book_id' => 1, 'deleted_at' => Carbon::now()]);
        create(Book::class);

        $res = $this->updateNote(1, ['deleted_at' => null]);
        $res->assertStatus(200);
        $this->assertDatabaseHas((new Note())->getTable(), ['id' => 1, 'deleted_at' => null]);
    }

    public function testUpdateNote()
    {
        $this->login();

        create(Note::class, ['book_id' => 1]);
        create(Book::class);

        $book = Book::find(1);
        $res = $this->updateNote(1, ['page' => 9999]);
        $res->assertStatus(422)->assertJsonCount(1, 'errors');
        $this->assertJsonContains("不能超过{$book->total}页", $res->getContent());

        $updateData = [
            'page'         => 2,
            'title'        => 'update tit',
            'desc'         => 'update desc',
            'content'      => 'update content',
            'html_content' => 'update html_content',
        ];

        $res = $this->updateNote(1, $updateData);
        $res->assertStatus(200);

        $this->assertDatabaseHas((new Note())->getTable(), $updateData + ['id' => 1]);
    }

    public function testUpdateNoteWithTags()
    {
        $this->login();

        $note = create(Note::class, ['book_id' => 1]);
        create(Book::class);

        $note->tags()->create(['name' => 'old attached tag']);
        $existsTag = create(Tag::class)->toArray();
        $newTag = 'new tag';

        $res = $this->updateNote($note->id, [
            'tags' => [$existsTag, $newTag],
        ]);
        $res->assertStatus(200);

        $this->assertDatabaseHas('tags', ['name' => 'new tag']);

        $p = [
            'target_id'   => $note->id,
            'target_type' => 'notes',
        ];

        $p['tag_id'] = $existsTag['id'];
        $this->assertDatabaseHas('model_tags', $p);

        $p['tag_id'] = 3;
        $this->assertDatabaseHas('model_tags', $p);

        $p['tag_id'] = 1;
        $this->assertDatabaseMissing('model_tags', $p);
    }
}
