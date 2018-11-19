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
    // 去掉 code 标签以及其中的所有内容
    $html_content = preg_replace('/<code.*?>[\s|\S]*?<\/code>/', '', $html_content);

    // 然后去掉所有标签
    $content = strip_tags($html_content);

    if ($length !== null) {
        $content = substr($content, 0, $length);
    }

    return $content;
}
