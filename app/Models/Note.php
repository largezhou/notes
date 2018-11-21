<?php

namespace App\Models;

use App\Traits\CanHide;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Note extends Model
{
    use SoftDeletes;
    use CanHide;

    protected $fillable = ['book_id', 'page', 'title', 'desc', 'content', 'html_content', 'hidden', 'deleted_at'];

    public static function boot()
    {
        parent::boot();

        // 博客和笔记用同一张表，有 book_id 的为笔记
        static::addGlobalScope('notes', function (Builder $builder) {
            $builder->where('book_id', '<>', 0);
        });

        static::saving(function (Note $note) {
            if (!$note->desc) {
                $note->desc = get_desc($note->html_content, 100);
            }
        });
    }

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
