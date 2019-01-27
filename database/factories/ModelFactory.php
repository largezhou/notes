<?php

use Faker\Generator as Faker;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

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
    static $cover;
    if (!$cover) {
        $file = UploadedFile::fake()->image('cover.jpg', 240, 320);
        $name = md5_file($file);
        Storage::drive('public')->putFileAs('uploads', $file, "{$name}.jpg");
        $cover = "/uploads/{$name}.jpg";
    }

    $total = mt_rand(200, 900);
    $read = mt_rand(5, $total - 100);

    return [
        'title'      => $faker->sentence,
        'started_at' => $faker->dateTimeBetween('-2 months')->format('Y-m-d'),
        'created_at' => $faker->dateTimeBetween('-2 months')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-2 months')->format('Y-m-d H:i:s'),
        'read'       => $read,
        'total'      => $total,
        'cover'      => $cover,
        'deleted_at' => null,
        'hidden'     => false,
    ];
});

$factory->define(App\Models\Note::class, function (Faker $faker) {
    return [
        'book_id'      => mt_rand(1, 1000),
        'title'        => $faker->sentence,
        'content'      => $faker->paragraph(10),
        'html_content' => '<h1>HTML_CONTENT</h1>' . $faker->paragraph(10),
        'page'         => mt_rand(1, 1000),
        'created_at'   => $faker->dateTimeBetween('-2 months')->format('Y-m-d H:i:s'),
        'updated_at'   => $faker->dateTimeBetween('-2 months')->format('Y-m-d H:i:s'),
        'deleted_at'   => null,
        'hidden'       => false,
    ];
});

$factory->define(App\Models\Tag::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});

$factory->define(App\Models\Post::class, function (Faker $faker) {
    return [
        'book_id'      => 0,
        'page'         => 0,
        'title'        => $faker->sentence,
        'desc'         => $faker->sentence,
        'content'      => $faker->paragraph(10),
        'html_content' => '<h1>HTML_CONTENT</h1>' . $faker->paragraph(10),
        'created_at'   => $faker->dateTimeBetween('-2 months')->format('Y-m-d H:i:s'),
        'updated_at'   => $faker->dateTimeBetween('-2 months')->format('Y-m-d H:i:s'),
        'deleted_at'   => null,
        'hidden'       => false,
    ];
});
