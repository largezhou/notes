<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'title'      => 'bail|required|string|max:255',
            'total'      => 'bail|required|integer|between:1,10000',
            'read'       => 'bail|nullable|integer|min:0|lte:total',
            'started_at' => 'bail|nullable|date',
            'cover'      => 'bail|required|image',
            'hidden'     => 'filled|boolean',
        ];

        switch ($this->method()) {
            case 'PUT':
                return array_only($rules, $this->keys());
            default:
                return $rules;
        }
    }

    public function attributes()
    {
        return [
            'title'      => '书名',
            'total'      => '总页数',
            'read'       => '已读',
            'started_at' => '开始时间',
            'cover'      => '封面',
            'hidden'     => '隐藏',
        ];
    }

    public function messages()
    {
        return [
            'cover.required' => ':attribute要传的',
        ];
    }
}
