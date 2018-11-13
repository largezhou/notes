<?php

namespace App\Filters;

use App\Models\Book;

class BookFilter extends Filter
{
    protected $filters = ['recent', 'edit_mode'];

    /**
     * 书籍挂件的书籍列表
     */
    protected function recent()
    {
        $this->builder->limit(Book::VERY_RECENT_COUNT);
    }
}
