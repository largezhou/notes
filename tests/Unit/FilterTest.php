<?php

namespace Tests\Unit;

use App\Filters\BookFilter;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterTest extends TestCase
{
    use DatabaseMigrations;

    public function testEditMode()
    {
        create(Book::class, [], 10);
        Book::unguard();
        Book::find(1)->update(['hidden' => true]);
        Book::find(2)->update(['deleted_at' => Carbon::now()]);

        $request = \Mockery::mock(Request::class, ['only' => ['edit_mode' => null]]);

        $books = Book::filter(new BookFilter($request))->get();

        // 未登录就算请求中有edit_mode字段，也不能看到隐藏和软删除的
        $this->assertEquals(8, $books->count());

        $user = create(User::class);
        $this->actingAs($user);

        $books = Book::filter(new BookFilter($request))->get();
        $this->assertEquals(10, $books->count());
    }
}
