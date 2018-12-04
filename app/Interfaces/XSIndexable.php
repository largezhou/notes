<?php

namespace App\Interfaces;

interface XSIndexable
{
    /**
     * 迅搜中 notes 项目的 title 字段内容
     *
     * @return string
     */
    public function xsTitle(): string;

    /**
     * content 字段内容
     *
     * @return string
     */
    public function xsContent(): string;

    /**
     * id 字段内容
     *
     * @return string
     */
    public function xsId(): string;

    /**
     * 模型保存时，脏检测的字段，当该字段有变化时，才会更新索引
     *
     * @return array
     */
    public function xsIndexFields(): array;

    /**
     * 返回文档数据
     *
     * @return array
     */
    public function xsDocData(): array;
}
