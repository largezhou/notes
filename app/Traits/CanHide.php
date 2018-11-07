<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CanHide
{
    public static function bootCanHide()
    {
        if (auth()->guest()) {
            static::addGlobalScope('onlyShown', function (Builder $builder) {
                $builder->where('hidden', false);
            });
        }
    }

    public function scopeWithHidden(Builder $query)
    {
        $query->withoutGlobalScope('onlyShown');
    }

    public function scopeShowAll(Builder $query)
    {
        parent::scopeShowAll($query);
        $query->withHidden();
    }
}
