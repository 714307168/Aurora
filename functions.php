<?php
/**
 * Aurora 主题函数
 *
 * @package Aurora
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Aurora
{
    private static $words = array(
        'zh-CN' => array(
            'home' => '首页', 'categories' => '分类', 'rss' => '订阅', 'search' => '搜点什么…',
            'theme_toggle' => '切换亮/暗主题', 'menu' => '菜单', 'breadcrumb' => '面包屑导航',
            'views' => '次阅读', 'reading' => '约 %d 字 · %d 分钟', 'related' => '相关文章',
            'no_more' => '没有了', 'like' => '喜欢', 'favorite' => '收藏', 'reward' => '支持作者',
            'toc' => '目录', 'hot' => '热门文章', 'tags' => '标签云', 'archive' => '归档',
            'comments_off' => '评论已关闭', 'comment' => '评论', 'back_top' => '回到顶部',
            'main_nav' => '主导航', 'post_nav' => '文章翻页', 'footer_tagline' => '技术分享，从热爱开始',
            'no_comments' => '暂无评论', 'one_comment' => '1 条评论', 'many_comments' => '%d 条评论',
            'your_comment' => '你的评论', 'comment_content' => '留下你的想法…', 'name' => '昵称',
            'email' => '邮箱', 'website' => '网站', 'optional' => '选填', 'signed_in_as' => '登录身份：',
            'logout' => '退出', 'submit_comment' => '发表评论', 'prev_page' => '前一页', 'next_page' => '后一页',
            'code' => '代码', 'copy' => '复制', 'copied' => '已复制', 'expand_lines' => '展开 %d 行'
        ),
        'en-US' => array(
            'home' => 'Home', 'categories' => 'Categories', 'rss' => 'RSS', 'search' => 'Search…',
            'theme_toggle' => 'Toggle light/dark theme', 'menu' => 'Menu', 'breadcrumb' => 'Breadcrumb',
            'views' => 'views', 'reading' => 'About %d words · %d min', 'related' => 'Related posts',
            'no_more' => 'No more posts', 'like' => 'Like', 'favorite' => 'Save', 'reward' => 'Support the author',
            'toc' => 'Contents', 'hot' => 'Popular posts', 'tags' => 'Tags', 'archive' => 'Archive',
            'comments_off' => 'Comments are closed', 'comment' => 'comments', 'back_top' => 'Back to top',
            'main_nav' => 'Main navigation', 'post_nav' => 'Post navigation', 'footer_tagline' => 'Sharing technology, driven by passion',
            'no_comments' => 'No comments yet', 'one_comment' => '1 comment', 'many_comments' => '%d comments',
            'your_comment' => 'Your comment', 'comment_content' => 'Share your thoughts…', 'name' => 'Name',
            'email' => 'Email', 'website' => 'Website', 'optional' => 'optional', 'signed_in_as' => 'Signed in as: ',
            'logout' => 'Log out', 'submit_comment' => 'Post comment', 'prev_page' => 'Previous', 'next_page' => 'Next',
            'code' => 'Code', 'copy' => 'Copy', 'copied' => 'Copied', 'expand_lines' => 'Expand %d lines'
        )
    );

    public static function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function lang()
    {
        $lang = Helper::options()->aurora_lang;
        return isset(self::$words[$lang]) ? $lang : 'zh-CN';
    }

    public static function t($key, $args = array())
    {
        $lang = self::lang();
        $value = isset(self::$words[$lang][$key]) ? self::$words[$lang][$key] : $key;
        return $args ? vsprintf($value, $args) : $value;
    }

    public static function absolute_url($url)
    {
        $url = trim(html_entity_decode((string)$url, ENT_QUOTES, 'UTF-8'));
        if ($url === '') return null;
        if (preg_match('#^https?://#i', $url)) return $url;
        if (strpos($url, '//') === 0) return 'https:' . $url;
        return rtrim(Helper::options()->siteUrl, '/') . '/' . ltrim($url, '/');
    }

    /** 从正文提取首图；没有图片时返回 null，不用站点 Logo 冒充封面。 */
    public static function content_image($source)
    {
        if (is_array($source)) {
            $text = isset($source['text']) ? (string)$source['text'] : '';
        } elseif (is_object($source)) {
            $text = (string)$source->text;
        } else {
            $text = (string)$source;
        }
        $patterns = array(
            '/<img[^>]+src=["\']([^"\']+)["\']/i',
            '/!\[[^\]]*\]\(([^\s\)]+)(?:\s+["\'][^"\']*["\'])?\)/',
            '/(https?:\/\/[^\s"\']+\.(?:jpg|jpeg|png|webp|gif))/i'
        );
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $match)) return self::absolute_url($match[1]);
        }
        return null;
    }

    /** 文章首图：正文无图时使用主题 Logo 作为社交分享兜底。 */
    public static function og_image($widget)
    {
        $image = ($widget->is('post') || $widget->is('page')) ? self::content_image($widget) : null;
        if ($image) return $image;
        return self::absolute_url(rtrim((string)Helper::options()->themeUrl, '/') . '/assets/logo.svg');
    }

    /** 首页统计，查询失败时安全返回 0。 */
    public static function site_stats()
    {
        $stats = array('posts' => 0, 'categories' => 0, 'tags' => 0);
        try {
            $db = Typecho_Db::get();
            $post = $db->fetchRow($db->select('COUNT(cid) AS total')->from('table.contents')
                ->where('type = ?', 'post')->where('status = ?', 'publish'));
            $category = $db->fetchRow($db->select('COUNT(mid) AS total')->from('table.metas')->where('type = ?', 'category'));
            $tag = $db->fetchRow($db->select('COUNT(mid) AS total')->from('table.metas')->where('type = ?', 'tag'));
            $stats['posts'] = isset($post['total']) ? (int)$post['total'] : 0;
            $stats['categories'] = isset($category['total']) ? (int)$category['total'] : 0;
            $stats['tags'] = isset($tag['total']) ? (int)$tag['total'] : 0;
        } catch (Exception $error) {
            return $stats;
        }
        return $stats;
    }

    /** 首页 Hero 使用的最新文章。 */
    public static function featured_post()
    {
        try {
            $db = Typecho_Db::get();
            return $db->fetchRow($db->select('cid', 'title', 'text', 'created')->from('table.contents')
                ->where('type = ?', 'post')->where('status = ?', 'publish')
                ->order('created', Typecho_Db::SORT_DESC)->limit(1));
        } catch (Exception $error) {
            return null;
        }
    }

    public static function canonical($widget)
    {
        if ($widget->is('post') || $widget->is('page')) {
            return (string)$widget->permalink;
        }
        $site = rtrim((string)Helper::options()->siteUrl, '/');
        $path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';
        return $site . '/' . ltrim((string)$path, '/');
    }

    public static function post_url($cid)
    {
        return rtrim(Helper::options()->siteUrl, '/') . '/index.php/archives/' . (int)$cid . '/';
    }

    /** Cookie 去重的文章浏览量；同一浏览器每篇只累计一次。views 列由部署迁移负责，前台绝不执行 DDL。 */
    public static function post_views($widget, $increment = true)
    {
        $db = Typecho_Db::get();
        $cid = (int)$widget->cid;
        try {
            $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
        } catch (Exception $error) {
            return 0;
        }
        $views = isset($row['views']) ? (int)$row['views'] : 0;
        if (!$increment || !$widget->is('post')) return $views;

        $visited = Typecho_Cookie::get('aurora_post_views');
        $visited = $visited ? array_filter(explode(',', $visited), 'strlen') : array();
        if (!in_array((string)$cid, $visited, true)) {
            try {
                $db->query($db->update('table.contents')
                    ->expression('views', 'views + 1', false)
                    ->where('cid = ?', $cid));
                $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
                $views = isset($row['views']) ? (int)$row['views'] : $views;
                $visited[] = (string)$cid;
                $visited = array_slice(array_unique($visited), -200);
                Typecho_Cookie::set('aurora_post_views', implode(',', $visited));
            } catch (Exception $error) {
                return $views;
            }
        }
        return $views;
    }

    public static function json_ld($widget)
    {
        if (!$widget->is('post')) return null;
        $site = Helper::options();
        $canonical = self::canonical($widget);
        $category = '';
        if (!empty($widget->categories) && isset($widget->categories[0]['name'])) {
            $category = $widget->categories[0]['name'];
        }
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'BlogPosting', '@id' => $canonical . '#article',
                    'headline' => (string)$widget->title,
                    'description' => trim(strip_tags((string)$widget->description)),
                    'datePublished' => date('c', (int)$widget->created),
                    'dateModified' => date('c', (int)$widget->modified),
                    'mainEntityOfPage' => $canonical,
                    'image' => array(self::og_image($widget)),
                    'author' => array('@type' => 'Person', 'name' => (string)$widget->author->screenName),
                    'publisher' => array('@type' => 'Person', 'name' => (string)$site->title)
                ),
                array(
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => array_values(array_filter(array(
                        array('@type' => 'ListItem', 'position' => 1, 'name' => self::t('home'), 'item' => (string)$site->siteUrl),
                        $category ? array('@type' => 'ListItem', 'position' => 2, 'name' => $category) : null,
                        array('@type' => 'ListItem', 'position' => $category ? 3 : 2, 'name' => (string)$widget->title, 'item' => $canonical)
                    )))
                )
            )
        );
        return json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    }
}

/** 主题设置：交互功能默认关闭，保持当前博客“关闭评论”的既有策略。 */
function themeConfig($form)
{
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Radio('aurora_sidebar',
        array('1' => _t('开启'), '0' => _t('关闭')), '1', _t('显示右侧栏'), _t('关闭后内容区单列居中')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Radio('aurora_copy',
        array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('页脚版权信息'), _t('备案号始终保留')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Radio('aurora_lang',
        array('zh-CN' => _t('简体中文'), 'en-US' => _t('English')), 'zh-CN', _t('主题界面语言')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_beian',
        NULL, '', _t('页脚备案号'), _t('支持含链接的备案 HTML；留空不显示')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_reward_image',
        NULL, '', _t('赞助二维码图片 URL'), _t('留空则不显示支持作者区域')));

    $form->addInput(new Typecho_Widget_Helper_Form_Element_Radio('aurora_comment_provider',
        array('off' => _t('关闭'), 'native' => _t('Typecho 原生'), 'giscus' => _t('Giscus'), 'artalk' => _t('Artalk')),
        'off', _t('评论服务'), _t('默认关闭；启用第三方服务前请先填写下方参数')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_giscus_repo', NULL, '', _t('Giscus Repo'), _t('如 owner/repo')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_giscus_repo_id', NULL, '', _t('Giscus Repo ID')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_giscus_category', NULL, 'Announcements', _t('Giscus Category')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_giscus_category_id', NULL, '', _t('Giscus Category ID')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_artalk_server', NULL, '', _t('Artalk Server URL')));
    $form->addInput(new Typecho_Widget_Helper_Form_Element_Text('aurora_artalk_site', NULL, '', _t('Artalk Site'), _t('Artalk 中登记的站点名称')));
}
