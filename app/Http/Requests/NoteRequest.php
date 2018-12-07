<?php

namespace App\Http\Requests;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function rules()
    {
        if ($this->isMethod('post')) {
            $book = $this->route('book');
        } else {
            $book = $this->route('note')->book;
        }

        $rules = [
            'page'         => 'bail|required|integer|max:' . $book->total,
            'desc'         => 'bail|nullable|string|max:255',
            'content'      => 'bail|required|string|max:60000',
            'html_content' => 'bail|required|string|max:60000',
            'tags'         => 'array',
        ];

        if ($this->isMethod('put')) {
            $rules = array_only($rules, $this->keys());
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'page'    => '页数',
            'desc'    => '描述',
            'content' => '内容',
            'tags'    => '标签',
        ];
    }

    public function messages()
    {
        return [
            'page.max'   => ':attribute不能超过:max页',
            'tags.array' => ':attribute数据格式不对',
        ];
    }
}
