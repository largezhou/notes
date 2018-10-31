<?php

namespace App\Models;

use App\Filters\BookFilter;
use App\Traits\CanHide;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;
    use CanHide;

    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
        'started_at' => 'datetime:Y-m-d',
    ];

    /**
     * 书籍挂件的书籍数量
     */
    const VERY_RECENT_COUNT = 5;

    protected $fillable = ['title', 'total', 'read', 'started_at', 'cover', 'hidden'];

    public static function getBooks()
    {
        return static::orderBy('updated_at', 'desc')
            ->filter(app(BookFilter::class))->get();
    }

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
