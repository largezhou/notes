<?php

namespace App\Models;

use App\Traits\CanHide;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseNote extends Model
{
    protected $table = 'notes';

    use SoftDeletes;
    use CanHide;

    public static function boot()
    {
        parent::boot();

        static::addTypeGlobalScope();

        static::saving(function (BaseNote $note) {
            if (!$note->desc) {
                $note->desc = get_desc($note->html_content, 100);
            }
        });
    }

    protected static function addTypeGlobalScope()
    {
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
