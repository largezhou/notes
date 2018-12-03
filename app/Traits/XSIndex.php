<?php

namespace App\Traits;

use App\Interfaces\XSIndexable;
use Illuminate\Database\Eloquent\Model;
use XSDocument;
use XSException;

trait XSIndex
{
    public static function bootXSIndex()
    {
        static::saved(function (XSIndexable $model) {
            if (app()->runningUnitTests()) {
                return;
            }

            $fields = array_merge($model->xsIndexFields(), ['hidden', 'deleted_at']);

            if (!$model->isDirty($fields)) {
                return;
            }

            $xs = app('XS');
            try {
                // TODO 这一步比较耗时，之后放到队列中
                $index = $xs->index;
            } catch (XSException $e) {
                report($e);

                return;
            }

            $doc = new XSDocument();

            $doc->setFields($model->xsDocData());

            // 如果数据是新建的，则使用 add ，官方说性能会快一点
            if ($model->wasRecentlyCreated) {
                $index->add($doc);
            } else {
                $index->update($doc);
            }

            $index->close();
        });
    }

    public function xsDocData(): array
    {
        return [
            'id'      => strtolower($this->xsId()),
            'title'   => strtolower($this->xsTitle()),
            'content' => strtolower($this->xsContent()),
            'hidden'  => (int) (bool) $this->getAttribute('hidden'),
            'deleted' => (int) (bool) $this->deleted_at,
        ];
    }
}
