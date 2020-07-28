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

    protected static function boot()
    {
        parent::boot();

        static::updated(function (Book $model) {
            $read = $model->read - $model->getOriginal('read');
            if ($read > 0) {
                $model
                    ->readRecords()
                    ->create([
                        'read' => $read,
                    ]);
            }
        });

        static::saved(function (Book $model) {
            if (
                $model->isDirty(['hidden', 'deleted_at'])
                && !app()->runningUnitTests()
                && !app()->runningInConsole()
            ) {
                $model->updateNotesXSIndex();
            }
        });

        static::deleted(function (Book $model) {
            if (
                $model->forceDeleting === false
                && !app()->runningUnitTests()
                && !app()->runningInConsole()
            ) {
                $model->updateNotesXSIndex();
            }
        });
    }

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

    public function readRecords()
    {
        return $this->hasMany(ReadRecord::class);
    }

    /**
     * 更新笔记的索引内容中 book_hidden 和 book_deleted
     */
    protected function updateNotesXSIndex()
    {
        $index = static::xsGetIndexIns();
        if (!$index) {
            return;
        }

        $this->notes->each(function (Note $note) use ($index) {
            $note->setRelation('book', $this);
            static::xsUpdate($index, $note);
        });
    }
}
