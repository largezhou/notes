<?php

namespace App\Http\Controllers;

use App\Models\BaseNote;
use App\Models\Note;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class TestController extends Controller
{
    const CUT_LENGTH = 50;

    public function test(Request $request, $path = null)
    {
        $content = '示例：本站的首页，书籍列表页，博客列表页，都使用了该效果\n\n\n\n路由设置\nconst <em>router</em> = new <em>router</em>({\n  // 省略...\n  scrollbehavior(to, from, savedposition) {\n    // savedposition 为 null 表示页面是跳转的，而不是历史记录中 `前进` 或 `后退`\n\n    // 如果页面是跳转的，则标记前往的路由需要重新加载数据\n    // 否则不需要\n    to.meta.needreload = !savedposition\n\n    return savedposition || { x: 0, y: 0 }\n  },\n  // 省略...\n})\n\n页面 <em>router</em>-view 设置\n&lt;keep-alive&gt;\n  &lt;<em>router</em>-view v-if=\"$route.meta.keepalive\" :key=\"$route.name\"/&gt;\n&lt;/keep-alive&gt;\n&lt;<em>router</em>-view v-if=\"!$route.meta.keepalive\"/&gt;\n\n页面获取数据的方式，可将下面代码独立成一个mixins\n/**\n * 当路由为点击链接跳转，而非浏览器前进后退时，标记路由需要重新加载数据\n */\nexport default {\n  created() {\n    // 保存组件的初始数据，用来更新数据前先清空当前数据\n    this.olddata = json.stringify(this.$data)\n\n    // 由于组件 vue 自带的 _inactive 字段更新的时机，要比 editmode 变化的要早\n    // 注意：由于 editmode 的方式变了，这里不知道还需不需要这个 $active\n    // 所以当用一个处于编辑模式的页面，点击链接跳转到一个被缓存的组件时\n    // 在 editmode 的侦听中，_inactive 已经为 false 了，这就会执行 getdata 方法，然而 activated 钩子中也会执行一次\n\n    // 自己维护的话，激活和非激活钩子函数执行的时机，要比 editmode 侦听的要晚，所以当侦听到 editmode 改变时，$active 字段还是为 false\n    // 就不会执行 getdata 方法\n    this.$active = true\n  },\n\n  deactivated() {\n    this.$active = false\n  },\n  activated() {\n    this.$active = true\n    this.$nexttick(() =&gt; {\n      const meta = this.$route.meta\n      // 如果页面缓存了，且不是【不需要刷新】状态，则刷新\n      // needreload === undefined 表示页面首次进入，比如在当前页面按了浏览器刷新，此时肯定要获取数据\n      // needreload === true 表示页面是由其他页面点击链接跳转过来了的。该值是在路由的滚动行为函数中赋予的true，也需要重新获取数据\n      // needreload === false 表示页面是通过浏览器前进或后退进入的。赋值时机同上，表示不需要重新获取数据\n      if (meta.keepalive &amp;&amp; meta.needreload !== false) {\n        log("in reload data mixin")\n        // 清空旧数据\n        object.assign(this.$data, json.parse(this.olddata))\n        // 重新获取数据，每个使用了该混入的页面，都要定义一个 getdata 获取数据方法\n        this.getdata()\n      }\n    })\n  },\n}\n\n使用了该效果的页面，如果要监视路由变化，重新获取数据，需要做如下处理\nexport default {\n  watch: {\n    $route() {\n      this.$active &amp;&amp; this.getdata()\n    },\n  },\n}\n\n其他\n\n在某些情况下，可能会出现获取两次数据的情况，暂时没发现。。。\n使用了该效果的页面，在 webpack 热重载时，请求数据后，不会渲染页面，不知道为啥，f5刷新即可。';

        $content = $this->cutContent($content);

        return $content;
    }

    protected function cutContent($content)
    {
        $emPos = mb_strpos($content, '<em>');

        if ($emPos === false) {
            return mb_substr($content, 0, self::CUT_LENGTH)
                . (self::CUT_LENGTH >= mb_strlen($content) ? '' : '...');
        }

        $cutStart = $emPos - self::CUT_LENGTH / 2;
        $cutStart = $cutStart > 0 ? $cutStart : 0;

        $cutEnd = strpos($content, '</em>', $emPos) + self::CUT_LENGTH / 2;

        return ($cutStart > 0 ? '...' : '')
            . mb_substr($content, $cutStart, $cutEnd - $cutStart)
            . ($cutEnd >= mb_strlen($content) ? '' : '...');
    }
}
