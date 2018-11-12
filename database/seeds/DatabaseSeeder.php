<?php

use App\Models\Note;
use App\Models\Tag;
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
        $this->call(TagTableSeeder::class);
    }
}

class BookTableSeeder extends Seeder
{
    public function run()
    {
        Book::truncate();
        Note::truncate();
        Tag::truncate();

        factory(Book::class, 10)->create()->each(function (Book $book) {
            $notesData = factory(Note::class, 10)->make()->each(function (Note $note) use ($book) {
                $note->page = mt_rand(1, $book->read);
            });

            $book->notes()->saveMany($notesData);
        });
    }
}

class TagTableSeeder extends Seeder
{
    public function run()
    {
        Tag::truncate();
        DB::table('model_tags')->truncate();

        $tags = factory(Tag::class, 50)->create()->pluck('id')->toArray();
        Note::all()->each(function (Note $note) use ($tags) {
            $note->tags()->attach(array_random($tags, 5));
        });
    }
}
