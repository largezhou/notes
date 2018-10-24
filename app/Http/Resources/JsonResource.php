<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as R;

class JsonResource extends R
{
    public static $wrap = null;

    /**
     * 这里的字段会排除显示
     * @var array
     */
    protected $except = [
        'hidden',
        'deleted_at',
    ];

    /**
     * 这里的字段会显示，优先级比 except 高
     * @var array
     */
    protected $only = null;

    public function __construct($resource)
    {
        parent::__construct($resource);

        // 只有在登录的情况下才能启用编辑模式
        if (request()->has('edit_mode') && auth()->check()) {
            $this->except = null;
        }
    }

    /**
     * 排除 $keys 中的字段
     *
     * @param string|array $keys
     *
     * @return $this
     */
    public function except($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $this->except = $keys;

        return $this;
    }

    /**
     * 只显示 $keys 中的字段
     *
     * @param string|array $keys
     *
     * @return $this
     */
    public function only($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $this->only = $keys;

        return $this;
    }

    /**
     * 根据 $except 或者 $only 过滤字段
     *
     * @param $data
     *
     * @return array
     */
    protected function filterKeys($data)
    {
        if (is_array($this->only)) {
            return collect($data)->only($this->only)->toArray();
        }

        if (is_array($this->except)) {
            return collect($data)->forget($this->except)->toArray();
        }

        return $data;
    }

    public static function collection($resource)
    {
        return new ResourceCollection($resource, get_called_class());
    }

    public function toArray($request)
    {
        return $this->filterKeys($this->data($request));
    }

    /**
     * 返回资源的所有数据，toArray时，会根据设置进行过滤
     *
     * @param $request
     */
    public function data($request)
    {

    }
}
