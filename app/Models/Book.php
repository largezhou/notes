<?php

namespace App\Models;

use App\Interfaces\XSIndexable;
use App\Traits\CanHide;
use App\Traits\XSIndex;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model implements XSIndexable
{
    use SoftDeletes;
    use CanHide;
    use XSIndex;

    /**
     * 书籍挂件的书籍数量
     */
    const VERY_RECENT_COUNT = 5;

    protected $fillable = ['title', 'total', 'read', 'started_at', 'cover', 'hidden', 'deleted_at'];

    public function setReadAttribute($value)
    {
        if (is_null($value)) {
            $value = 0;
        }

        $this->attributes['read'] = $value;
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function delete()
    {
        \DB::beginTransaction();

        if ($this->forceDeleting) {
            $this->notes->each->forceDelete();
        }

        $res = parent::delete();
        \DB::commit();

        return $res;
    }

    public function xsContent(): string
    {
        return '';
    }

    public function xsTitle(): string
    {
        return $this->title;
    }

    public function xsId(): string
    {
        return "book-{$this->id}";
    }

    public function xsIndexFields(): array
    {
        return ['title'];
    }
}
