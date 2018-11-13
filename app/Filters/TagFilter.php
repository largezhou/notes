<?php

namespace App\Filters;

use App\Models\Tag;

class TagFilter extends Filter
{
    protected $filters = ['scope', 'q'];

    protected function scope($value)
    {
        switch ($value) {
            case 'hot':
                $this->builder->withCount('targets')->orderBy('targets_count', 'desc')->limit(Tag::HOT_COUNT);
                break;
            default:
                // null
        }
    }

    protected function q($value)
    {

    }
}
