<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|max:50|unique:tags,name,' . (int) $this->route('tag')->id,
        ];
    }

    public function attributes()
    {
        return [
            'name' => '名称',
        ];
    }
}
