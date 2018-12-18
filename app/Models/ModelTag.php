<?php

namespace App\Models;

class ModelTag extends Model
{
    public function baseNote()
    {
        return $this->belongsTo(BaseNote::class, 'target_id');
    }
}
