<?php

namespace App\Traits;

use App\Interfaces\XSIndexable;
use Illuminate\Database\Eloquent\Model;
use XSDocument;
use XSException;

trait XSIndex
{
    protected static function xsGetIndexIns()
    {
        $xs = app('XS');
        try {
            // TODO 这一步比较耗时，之后放到队列中
            $index = $xs->index;
        } catch (XSException $e) {
            report($e);

            return null;
        }

        return $index;
    }

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

            $index = static::xsGetIndexIns();
            if (!$index) {
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

        static::deleted(function (XSIndexable $model) {
            // 如果是软删除，则不用删除对应的索引
            if ($model->forceDeleting === false) {
                return;
            }

            // 其他情况，要么没有 forceDeleting 属性，要么为 true，都是彻底删除
            $index = static::xsGetIndexIns();
            if (!$index) {
                return;
            }

            $index->del($model->xsId());
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
