<?php

namespace Tests\Unit;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTagTest extends TestCase
{
    use DatabaseMigrations;

    public function testExample()
    {
        create(Tag::class);
        create(Note::class);

        $note = Note::first();
        $tag = Tag::first();

        $note->tags()->attach($tag->id);
        $this->assertDatabaseHas('model_tags', [
            'tag_id'      => $tag->id,
            'target_id'   => $note->id,
            'target_type' => Note::class,
        ]);

        $tags = $note->tags;
        $this->assertCount(1, $tags);
        $this->assertEquals($tag->id, $tags->first()->id);

        $notes = $tag->notes;
        $this->assertCount(1, $tags);
        $this->assertEquals($note->id, $notes->first()->id);
    }
}
