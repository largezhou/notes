<?php

namespace App\Filters;

class NoteFilter extends Filter
{
    protected $filters = ['_sort_field', 'search_page'];

    protected function searchPage($page)
    {
        $page = strtolower($page);

        // 检测是不是 12 或者 12f 这样的页数，带 f 的表示全匹配
        if ($page && preg_match('/^\d+f?$/', $page)) {
            if (strpos($page, 'f') !== false) {
                $this->builder->where('page', substr($page, 0, -1));
            } else {
                $this->builder->where('page', 'like', "%{$page}%");
            }
        }
    }
}
