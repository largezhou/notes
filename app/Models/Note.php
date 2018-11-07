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
}
