<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use XSDocument;
use XSException;

class SearchController extends Controller
{
    const CUT_LENGTH = 200;

    public function __construct()
    {
        $this->middleware('auth')->only([]);
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');

        if (!$q) {
            return [];
        }

        $xs = app('XS');
        try {
            $search = $xs->search;
        } catch (XSException $e) {
            if (config('app.debug')) {
                throw  $e;
            } else {
                report($e);

                return [];
            }
        }

        $q = "({$q})";

        // 软删除 和 隐藏的
        if (!auth()->check()) {
            $q .= ' AND hidden:0 AND deleted:0';
        } elseif (!$request->header('Edit-Mode')) {
            $q .= ' AND deleted:0';
        }

        $res = $search->setQuery($q)->search();

        $res = array_map(function (XSDocument $doc) use ($search) {
            $data = $doc->getFields();

            list($data['type'], $data['id']) = explode('-', $data['id']);
            $data['title'] = $search->highlight($data['title']);
            $data['content'] = $this->cutContent($search->highlight($data['content']));

            if (auth()->check()) {
                $data['hidden'] = (int) $data['hidden'];
                $data['deleted'] = (int) $data['deleted'];
            } else {
                unset($data['hidden']);
                unset($data['deleted']);
            }

            return $data;
        }, $res);

        $search->close();

        return $res;
    }

    /**
     * 截取高亮词前后部分内容返回，如果没有高亮词，则截取前面部分内容
     *
     * @param $content
     *
     * @return string
     */
    protected function cutContent($content)
    {
        $emPos = mb_strpos($content, '<em>');

        if ($emPos === false) {
            return mb_substr($content, 0, self::CUT_LENGTH)
                . (self::CUT_LENGTH >= mb_strlen($content) ? '' : '...');
        }

        $cutStart = $emPos - self::CUT_LENGTH / 2;
        $cutStart = $cutStart > 0 ? $cutStart : 0;

        $cutEnd = strpos($content, '</em>', $emPos) + self::CUT_LENGTH / 2;

        return ($cutStart > 0 ? '...' : '')
            . mb_substr($content, $cutStart, $cutEnd - $cutStart)
            . ($cutEnd >= mb_strlen($content) ? '' : '...');
    }
}
