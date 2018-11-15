<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * @var Book
     */
    protected $book;
    protected $hasRead;
    protected $hasTotal;

    public function getBook()
    {
        if ($this->isMethod('post')) {
            return null;
        }

        if (!$this->book) {
            $this->book = Book::editMode()->findOrFail($this->route('book'));
        }

        return $this->book;
    }

    public function validationData()
    {
        $this->handleHasReadOrTotal();

        return parent::validationData();
    }

    /**
     * 处理更新的字段中，read 和 total 有且只有其一的情况
     */
    protected function handleHasReadOrTotal()
    {
        $this->hasRead = $this->has('read');
        $this->hasTotal = $this->has('total');

        $book = $this->getBook();
        if (!$book) {
            return;
        }

        if ($this->hasRead && !$this->hasTotal) {
            $key = 'total';
            $val = (string) $book->total;
        } elseif ($this->hasTotal && !$this->hasRead) {
            $key = 'read';
            $val = (string) $book->read;
        } else {
            return;
        }

        $this->request->set($key, $val);
        $this->merge([$key => $val]);
    }

    public function rules()
    {
        $rules = [
            'title'      => 'bail|required|string|max:255',
            'total'      => 'bail|required|integer|between:1,10000',
            'started_at' => 'bail|nullable|date',
            'cover'      => 'bail|required|image',
            'hidden'     => 'filled|boolean',
        ];

        // 避免 total 没有验证成功时，验证 read 的 lte 规则，会报错的问题
        $total = $this->get('total');
        if (isset($total) && is_numeric($total)) {
            $rules['read'] = 'bail|nullable|integer|min:0|lte:total';
        }

        switch ($this->method()) {
            case 'PUT':
                if ($this->hasTotal && !$this->hasRead) {
                    $rules['total'] = 'bail|required|integer|between:1,10000|gte:read';
                }

                $rules = array_only($rules, $this->keys());
                break;
        }

        return $rules;
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

    protected function failedValidation(Validator $validator)
    {
        $validator = $this->newValidatorIfTotalHasFailed($validator);

        return parent::failedValidation($validator);
    }

    /**
     * 处理 total 验证没通过时，read 的 lte 验证错误信息有点问题的问题
     *
     * @param Validator $validator
     *
     * @return Validator|\Illuminate\Validation\Validator
     */
    protected function newValidatorIfTotalHasFailed(Validator $validator)
    {
        if ($validator->getMessageBag()->hasAny('total')) {
            $oldErrors = $validator->getMessageBag()->getMessages();
            unset($oldErrors['read']);

            $validator = \Validator::make([], []);

            foreach ($oldErrors as $key => $errors) {
                $validator->getMessageBag()->add($key, ...$errors);
            }
        }

        return $validator;
    }
}
