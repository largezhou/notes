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

    /**
     * 分离数据中的 已存在标签 和 新标签
     *
     * @param $data
     *
     * @return array [$exists, $new]
     */
    public static function separateTags($data)
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

    public function delete()
    {
        \DB::beginTransaction();
        $this->targets()->delete();
        return parent::delete();
    }
}
