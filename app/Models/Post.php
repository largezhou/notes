<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Post extends BaseNote
{
    protected $fillable = ['title', 'desc', 'content', 'html_content', 'hidden', 'deleted_at'];

    protected $attributes = [
        'page' => 0,
        'book_id' => 0,
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (Post $post) {
            if (!$post->desc) {
                $post->desc = get_desc($post->html_content, 100);
            }
        });
    }

    protected static function addTypeGlobalScope()
    {
        // 博客和笔记用同一张表，没 book_id 的为笔记
        static::addGlobalScope('posts', function (Builder $builder) {
            $builder->where('book_id', 0);
        });
    }

    public function xsId(): string
    {
        return "post-{$this->id}";
    }
}
