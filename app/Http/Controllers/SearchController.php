<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use XSDocument;
use XSException;

class SearchController extends Controller
{
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

        $res = $search->setQuery($q)->search();

        $res = array_map(function (XSDocument $doc) use ($search) {
            $data = $doc->getFields();

            list($type, $id) = explode('-', $data['id']);
            $title = $search->highlight($data['title']);
            $content = $search->highlight($data['content']);

            return compact('type', 'id', 'title', 'content');
        }, $res);

        $search->close();

        return $res;
    }
}
