<?php

namespace Tests;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function login(User $user = null)
    {
        $user = $user ?: create(User::class);
        $this->actingAs($user);

        return $user;
    }

    protected function prepareBooks()
    {
        create(Book::class, [], 10);
        Book::find(1)->delete();
        Book::find(2)->update(['hidden' => true]);
    }
}
