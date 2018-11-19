<?php

namespace App\Models;

use App\Filters\BookFilter;
use App\Traits\CanHide;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;
    use CanHide;

    /**
     * 书籍挂件的书籍数量
     */
    const VERY_RECENT_COUNT = 5;

    protected $fillable = ['title', 'total', 'read', 'started_at', 'cover', 'hidden'];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function setReadAttribute($value)
    {
        $this->attributes['read'] = $value ?? 0;
    }

    public function delete()
    {
        \DB::beginTransaction();

        if ($this->forceDeleting) {
            $this->notes->each->forceDelete();
        }

        $res = parent::delete();
        \DB::commit();

        return $res;
    }
}
