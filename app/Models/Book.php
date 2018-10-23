<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Book extends Model
{
    use SoftDeletes;

    /**
     * 书籍挂件的书籍数量
     */
    const VERY_RECENT_COUNT = 5;

    protected $fillable = ['title', 'total', 'read', 'started_at', 'cover'];

    public static function getBooks(Request $request)
    {
        $query = static::where('hidden', false)->orderBy('updated_at', 'desc');

        if ($veryRecent = $request->get('recent')) {
            $query = $query->limit(self::VERY_RECENT_COUNT);
        }

        if ($request->get('edit_mode')) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public static function addBook($data)
    {
        $data['hidden'] = false;
        static::unguard();

        return static::create($data);
    }
}
