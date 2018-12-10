<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Note extends BaseNote
{
    protected $fillable = ['book_id', 'page', 'desc', 'content', 'html_content', 'hidden', 'deleted_at'];

    protected static function addTypeGlobalScope()
    {
        // 博客和笔记用同一张表，有 book_id 的为笔记
        static::addGlobalScope('notes', function (Builder $builder) {
            $builder->where('book_id', '<>', 0);
        });
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function xsId(): string
    {
        return "note-{$this->id}";
    }

    public function xsTitle(): string
    {
        return $this->book->title . ' • ' . $this->page;
    }
}
