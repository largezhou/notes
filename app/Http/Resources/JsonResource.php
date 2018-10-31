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
    protected $except = null;

    /**
     * 这里的字段会显示，优先级比 except 高
     * @var array
     */
    protected $only = null;

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
        $collect = collect($data);

        if (is_array($this->only)) {
            $collect = $collect->only($this->only);
        } elseif (is_array($this->except)) {
            $collect = $collect->forget($this->except);
        }

        if (auth()->guest()) {
            $collect->forget(['hidden', 'deleted_at']);
        }

        return $collect->toArray();
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
