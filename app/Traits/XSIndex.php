<?php

namespace App\Traits;

use App\Interfaces\XSIndexable;
use XSDocument;
use XSException;
use XSIndex as XSIndexer;

trait XSIndex
{
    public static function bootXSIndex()
    {
        static::saved(function (XSIndexable $model) {
            if (app()->runningUnitTests() || app()->runningInConsole()) {
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

            static::xsUpdate($index, $model);

            $index->close();
        });

        static::deleted(function (XSIndexable $model) {
            $index = static::xsGetIndexIns();
            if (!$index) {
                return;
            }

            // 如果是软删除，则要更新索引
            // 否则删除索引
            if ($model->forceDeleting === false) {
                static::xsUpdate($index, $model);
            } else {
                $index->del($model->xsId());
            }
        });
    }

    /**
     * 获取迅搜索引实例
     *
     * @return XSIndexer|null
     */
    protected static function xsGetIndexIns()
    {
        $xs = app('XS');
        try {
            $index = $xs->index;
        } catch (XSException $e) {
            report($e);

            return null;
        }

        return $index;
    }

    /**
     * 更新索引
     *
     * @param XSIndexer   $index
     * @param XSIndexable $model
     */
    protected static function xsUpdate(XSIndexer $index, XSIndexable $model)
    {
        $doc = new XSDocument();

        $doc->setFields($model->xsDocData());

        // 如果数据是新建的，则使用 add ，官方说性能会快一点
        if ($model->wasRecentlyCreated) {
            $index->add($doc);
        } else {
            $index->update($doc);
        }
    }

    /**
     * 返回用于添加到 notes 项目索引文档的数据
     *
     * @return array
     */
    public function xsDocData(): array
    {
        return [
            'id'      => strtolower($this->xsId()),
            'title'   => strtolower($this->xsTitle()),
            'content' => strtolower($this->xsContent()),
            'hidden'  => (int) !!$this->getAttribute('hidden'),
            'book_hidden'  => 0,
            'deleted' => (int) !!$this->deleted_at,
            'book_deleted' => 0,
        ];
    }
}
