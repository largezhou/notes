<?php

/**
 * 从 html 内容中，去掉 code 标签的内容，并去掉所有标签，并返回指定长度
 *
 * @param string $html_content
 * @param int    $length
 *
 * @return string
 */
function get_desc(string $html_content, int $length = null): string
{
    // 去掉 pre 标签以及其中的所有内容
    $html_content = preg_replace('/<pre.*?>[\s|\S]*?<\/pre>/', '', $html_content);

    // 然后去掉所有标签
    $content = strip_tags($html_content);

    if ($length !== null) {
        $content = mb_substr($content, 0, $length);
    }

    return $content;
}

/**
 * 去掉地址中的协议，留下 ‘//’ 开头
 *
 * @param string $url
 *
 * @return string
 */
function noneProtocol(string $url): string
{
    $t = explode('//', $url);

    if (count($t) != 2) {
        return $url;
    }

    return '//' . $t[1];
}
