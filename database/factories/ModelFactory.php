<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\User::class, function (Faker $faker) {
    static $password;

    return [
        'username' => $faker->name,
        'password' => $password ?: $password = bcrypt('000000'),
    ];
});

$factory->define(App\Models\Book::class, function (Faker $faker) {
    $createdAt = Carbon::instance($faker->dateTimeBetween('-2 months'));
    $startedAt = Carbon::instance($createdAt)->addDays(mt_rand(-30, 0));
    $updatedAt = Carbon::instance($createdAt)->addDays(mt_rand(1, 30));
    $deletedAt = Carbon::instance($updatedAt)->addDays(10, 20);

    $total = mt_rand(200, 900);
    $read = mt_rand(0, $total);

    return [
        'title'      => $faker->sentence,
        'started_at' => $startedAt,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
        'read'       => $read,
        'total'      => $total,
        'cover'      => '',
        'deleted_at' => null,
        'hidden'     => false,
    ];
});
