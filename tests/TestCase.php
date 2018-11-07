<?php

namespace Tests;

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
}
