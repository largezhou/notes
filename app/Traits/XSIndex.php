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

            if (!$model->isDirty($model->xsIndexFields())) {
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

            $data = [
                'id'      => strtolower($model->xsId()),
                'title'   => strtolower($model->xsTitle()),
                'content' => strtolower($model->xsContent()),
            ];

            $doc->setFields($data);

            // 如果数据时新建的，则使用 add ，官方说性能会快一点
            if ($model->wasRecentlyCreated) {
                $index->add($doc);
            } else {
                $index->update($doc);
            }

            $index->close();
        });
    }
}
