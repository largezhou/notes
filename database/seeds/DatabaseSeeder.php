<?php

use App\Models\Note;
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
        Note::truncate();
        factory(Book::class, 10)->create()->each(function (Book $book) {
            $notes_count = mt_rand(1, 10);
            $notesData = factory(Note::class, $notes_count)->make()->each(function (Note $note) use ($book) {
                $note->page = mt_rand(1, $book->read);
            });

            $book->notes()->saveMany($notesData);
        });
    }
}
