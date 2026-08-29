<?php
/**
 * Aurora 主题函数
 *
 * @package Aurora
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Aurora
{
    /** 文章首图（og:image / 封面） */
    public static function og_image($widget)
    {
        if (preg_match('/(https?:\S+\.(jpg|jpeg|png|webp|gif))/i', $widget->text, $m)) {
            return $m[1];
        }
        return null;
    }

    /** 是否为文章（用于 meta keywords 计算） */
    public static function is_post($widget)
    {
        return $widget->is('post');
    }

    /** 获取独立页面/分类的辅助调用 */
    public static function sidebar_search($value = '')
    {
        return htmlspecialchars($value);
    }
}

/** 主题设置（可选）：开启/关闭侧栏、页脚版权文案 */
function aurora_theme_config($form)
{
    $sidebar = new Typecho_Widget_Helper_Form_Element_Radio('aurora_sidebar',
        array('1' => _t('开启'), '0' => _t('关闭')), '1', _t('显示右侧栏'), _t('关闭后内容区单列居中'));
    $form->addInput($sidebar);

    $copy = new Typecho_Widget_Helper_Form_Element_Radio('aurora_copy',
        array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('页脚版权信息'), _t('备案号始终保留'));
    $form->addInput($copy);

    $beian = new Typecho_Widget_Helper_Form_Element_Text('aurora_beian',
        NULL, '', _t('页脚备案号'), _t('留空则不显示。填写含链接的备案 HTML，如：<a href="https://beian.miit.gov.cn/" target="_blank">京ICP备00000000号</a>'));
    $form->addInput($beian);
}
