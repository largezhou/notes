<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title'        => 'bail|required|string|max:255',
            'desc'         => 'bail|nullable|string|max:255',
            'content'      => 'bail|required|string|max:60000',
            'html_content' => 'bail|required|string|max:60000',
            'tags'         => 'array',
        ];
    }

    public function attributes()
    {
        return [
            'title'   => '标题',
            'desc'    => '描述',
            'content' => '内容',
            'tags'    => '标签',
        ];
    }

    public function messages()
    {
        return [
            'tags.array' => ':attribute数据格式不对',
        ];
    }
}
