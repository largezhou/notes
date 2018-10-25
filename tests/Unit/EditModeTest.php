<?php

namespace Tests\Unit;

use App\Models\Book;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditModeTest extends TestCase
{
    use DatabaseMigrations;

    protected function prepareData()
    {
        create(Book::class, [], 10);
        Book::find(1)->delete();
        Book::find(2)->update(['hidden' => true]);
    }

    public function testNotInEditMode()
    {
        $this->prepareData();

        // 非编辑模式下，看不到 隐藏 和 软删除 的
        $this->assertEquals(8, Book::all()->count());
    }

    public function testInEditMode()
    {
        $this->prepareData();

        $this->assertEquals(10, Book::editMode()->get()->count());
    }
}
