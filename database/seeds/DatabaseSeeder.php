<?php

use Illuminate\Database\Seeder;
use App\Models\Book;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BookTableSeeder::class);
    }
}

class BookTableSeeder extends Seeder
{
    public function run()
    {
        Book::truncate();
        factory(Book::class, 10)->create();
    }
}
