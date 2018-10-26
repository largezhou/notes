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
function mock_request($mock)
{
    app()->bind(Request::class, function ($app) use ($mock) {
        return \Mockery::mock(Request::class, $mock);
    });
}
