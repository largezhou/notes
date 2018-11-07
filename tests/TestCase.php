<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function login(User $user = null)
    {
        $user = $user ?: create(User::class);
        $this->actingAs($user);

        return $user;
    }

    /**
     * 测试响应中的某个键的数据，是否按指定的顺序显示
     *
     * @param TestResponse $response json响应
     * @param string       $orderBy 排序字段
     * @param string       $orderType 排序类型 desc 或 asc
     * @param string|null  $key 数据中的那个键
     */
    protected function assertOrderBy(TestResponse $response, string $orderBy, string $orderType, string $key = null)
    {
        $data = json_decode($response->getContent(), true);
        if ($key) {
            $data = $data[$key];
        }

        $json = json_encode($data);

        $sortFunc = $orderType == 'desc' ? 'sortByDesc' : 'sortBy';
        $ordered = collect($data)->$sortFunc($orderBy);

        $orderedJson = json_encode($ordered);

        $this->assertEquals($orderedJson, $json, "按 ({$orderBy}) 的 ({$orderType}) 排序不对");
    }
}
