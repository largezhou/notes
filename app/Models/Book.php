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

    public static function addBook($data)
    {
        $data['hidden'] = false;

        return static::create($data);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
