<?php

namespace App\Models;

class Tag extends Model
{
    public $timestamps = false;

    public $fillable = ['name'];

    public function notes()
    {
        return $this->morphedByMany(Note::class, 'target', 'model_tags');
    }
}
