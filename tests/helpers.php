<?php

use Illuminate\Http\Request;

function create($class, $attributes = [], $times = null)
{
    return factory($class, $times)->create($attributes);
}

function make($class, $attributes = [], $times = null)
{
    return factory($class, $times)->make($attributes);
}

function raw($class, $attributes = [], $times = null)
{
    return factory($class, $times)->raw($attributes);
}

/**
 * 用mock请求，替换容器中的请求实例
 *
 * @param array $mock mock的数据或方法等
 */
function mock_request($mock = [])
{
    app()->bind(Request::class, function ($app) use ($mock) {
        return \Mockery::mock(Request::class, $mock);
    });

    app()->bind('request', function ($app) use ($mock) {
        return \Mockery::mock(Request::class, $mock);
    });
}

/**
 * 把字符串转成json，并去掉起止的双引号，主要用于在 json 中查找中文
 *
 * @param string $str
 *
 * @return bool|string
 */
function json_get_str(string $str)
{
    $str = json_encode($str);

    return substr($str, 1, strlen($str) - 2);
}
