<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\RequestActions;

class ReadRecordTest extends TestCase
{
    use DatabaseMigrations;
    use RequestActions;

    protected function refreshApplication()
    {
        // sqlite 中不支持 date_format 函数。
        // 临时使用 mysql 连接
        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=notes_test');

        parent::refreshApplication();
    }

    protected function createBookAndRecord()
    {
        $book = create(Book::class);
        $time = (string) Carbon::now();
        $book->readRecords()
            ->create(
                make(ReadRecord::class, [
                    'created_at' => $time,
                ])->toArray()
            );

        return $book;
    }

    public function testHiddenBookTimeline()
    {
        $book = $this->createBookAndRecord();
        // 隐藏这本书
        $book->update(['hidden' => true]);
        // 未登录的话，book_id 和 book 会被设为 null
        $this->getResources('read_records')
            ->assertStatus(200)
            ->assertJsonFragment([
                'book_id' => null,
                'book' => null,
            ]);

        $this->login();
        Model::clearBootedModels();

        // 登录了，可以看到隐藏的书
        $this->getResources('read_records')
            ->assertStatus(200)
            ->assertJsonFragment([
                'book_id' => 1,
                'book' => [
                    'id' => 1,
                    'title' => $book->title,
                ],
            ]);
    }

    public function testSoftDeletedBookTimeline()
    {
        $book = $this->createBookAndRecord();
        $book->delete();

        $this->getResources('read_records')
            ->assertStatus(200)
            ->assertJsonFragment([
                'book_id' => null,
                'book' => null,
            ]);

        $this->login();
        Model::clearBootedModels();

        // 即使登录了，软删除的书，也看不到书名
        $this->getResources('read_records')
            ->assertStatus(200)
            ->assertJsonFragment([
                'book_id' => null,
                'book' => null,
            ]);

        // 编辑模式
        $this->getResources('read_records', [], true)
            ->assertStatus(200)
            ->assertJsonFragment([
                'book_id' => 1,
                'book' => [
                    'id' => 1,
                    'title' => $book->title,
                ],
            ]);
    }
}
