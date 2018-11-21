<?php

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Post;

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
        $this->call(PostTableSeeder::class);
        $this->call(TagTableSeeder::class);
        $this->call(UserTableSeeder::class);
    }
}

class BookTableSeeder extends Seeder
{
    public function run()
    {
        factory(Book::class, 10)->create()->each(function (Book $book) {
            $notesData = factory(Note::class, 10)->make()->each(function (Note $note) use ($book) {
                $note->page = mt_rand(1, $book->read);
            });

            $book->notes()->saveMany($notesData);
        });
    }
}

class PostTableSeeder extends Seeder
{
    public function run()
    {
        factory(Post::class, 20)->create();
    }
}

class TagTableSeeder extends Seeder
{
    public function run()
    {
        $tags = factory(Tag::class, 50)->create()->pluck('id')->toArray();
        Note::all()->each(function (Note $note) use ($tags) {
            $note->tags()->attach(array_random($tags, 5));
        });
        Post::all()->each(function (Post $post) use ($tags) {
            $post->tags()->attach(array_random($tags, 5));
        });
    }
}

class UserTableSeeder extends Seeder
{
    public function run()
    {
        factory(User::class, 1)->create([
            'username'  => 'largezhou',
            'password' => bcrypt('000000'),
        ]);
    }
}
