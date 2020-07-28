<?php

namespace Tests\Traits;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

trait RequestActions
{
    /**
     * 请求资源集合
     *
     * @param string $name
     * @param array  $params
     * @param bool   $editMode
     *
     * @return TestResponse
     */
    protected function getResources(string $name, array $params = [], bool $editMode = false): TestResponse
    {
        return $this->json('get', route("{$name}.index"), $params, ['Edit-Mode' => $editMode]);
    }

    /**
     * 请求添加资源
     *
     * @param string $name
     * @param array  $data
     *
     * @return TestResponse
     */
    protected function postCreateResource(string $name, array $data = []): TestResponse
    {
        return $this->json('post', route("{$name}.store"), $data);
    }

    /**
     * 请求软删除一个资源
     *
     * @param string $name
     * @param int    $id
     * @param bool   $editMode
     *
     * @return TestResponse
     */
    protected function destroyResource(string $name, int $id, bool $editMode = false): TestResponse
    {
        return $this->json('delete', route("{$name}.destroy", [Str::singular($name) => $id]), [], ['Edit-Mode' => $editMode]);
    }

    /**
     * 请求彻底删除一个资源
     *
     * @param string $name
     * @param int    $id
     * @param bool   $editMode
     * @param string $deletedName
     *
     * @return TestResponse
     */
    protected function forceDestroyResource(string $name, int $id, bool $editMode = false, string $deletedName = null): TestResponse
    {
        $deletedName = $deletedName ?: 'deleted' . ucfirst(Str::singular($name));

        return $this->json('delete', route("{$name}.force_destroy", [$deletedName => $id]), [], ['Edit-Mode' => $editMode]);
    }

    /**
     * 请求获取单个资源
     *
     * @param string $name
     * @param int    $id
     * @param array  $params
     * @param bool   $editMode
     *
     * @return TestResponse
     */
    protected function getResource(string $name, int $id, array $params = [], bool $editMode = false): TestResponse
    {
        return $this->json('get', route("{$name}.show", [Str::singular($name) => $id]), $params, ['Edit-Mode' => $editMode]);
    }

    /**
     * 请求更新一个资源
     *
     * @param string $name
     * @param int    $id
     * @param array  $data
     * @param bool   $editMode
     *
     * @return TestResponse
     */
    protected function updateResource(string $name, int $id, array $data = [], bool $editMode = false): TestResponse
    {
        return $this->json('put', route("{$name}.update", [Str::singular($name) => $id]), $data, ['Edit-Mode' => $editMode]);
    }
}
