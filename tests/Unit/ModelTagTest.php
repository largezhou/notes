<?php

namespace Tests\Unit;

use App\Models\Note;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTagTest extends TestCase
{
    use DatabaseMigrations;

    public function testNoteAttachTags()
    {
        $tag = create(Tag::class);
        $note = create(Note::class);
        $post = create(Post::class);

        $note->tags()->attach($tag->id);
        $post->tags()->attach($tag->id);

        $this->assertDatabaseHas('model_tags', [
            'tag_id'      => $tag->id,
            'target_id'   => $note->id,
            'target_type' => 'notes',
        ]);

        $this->assertDatabaseHas('model_tags', [
            'tag_id'      => $tag->id,
            'target_id'   => $post->id,
            'target_type' => 'posts',
        ]);

        $tags = $note->tags;
        $this->assertCount(1, $tags);
        $this->assertEquals($tag->id, $tags->first()->id);

        $notes = $tag->notes;
        $this->assertCount(1, $notes);
        $this->assertEquals($note->id, $notes->first()->id);

        $posts = $tag->posts;
        $this->assertCount(1, $posts);
        $this->assertEquals($post->id, $posts->first()->id);

        $baseNotes = $tag->baseNotes;
        $this->assertCount(2, $baseNotes);
    }
}
