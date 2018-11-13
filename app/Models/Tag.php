<?php

namespace App\Models;

class Tag extends Model
{
    const HOT_COUNT = 10;

    public $timestamps = false;

    public $fillable = ['name'];

    public function notes()
    {
        return $this->morphedByMany(Note::class, 'target', 'model_tags');
    }

    public static function createTags($data)
    {
        $data = array_map(function ($d) {
            return is_array($d) ? $d['name'] : $d;
        }, $data);

        $exists = static::whereIn('name', $data)->pluck('name', 'id')->toArray();

        $new = array_diff($data, $exists);
        $new = array_map(function ($d) {
            return [
                'name' => $d,
            ];
        }, $new);

        return [$exists, $new];
    }

    public function targets()
    {
        return $this->hasMany(ModelTag::class);
    }
}
