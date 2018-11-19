<?php

namespace App\Models;

use App\Traits\CanHide;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;
    use CanHide;

    protected $fillable = ['book_id', 'page', 'title', 'desc', 'content', 'html_content', 'hidden'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'target', 'model_tags');
    }

    public function handleSyncTags($tags)
    {
        list($exists, $new) = Tag::separateTags($tags);
        $this->tags()->sync(array_keys($exists));
        $this->tags()->createMany($new);
    }

    public function delete()
    {
        \DB::beginTransaction();

        if ($this->forceDeleting) {
            $this->tags()->detach();
        }

        $res = parent::delete();
        \DB::commit();

        return $res;
    }
}
