#!/usr/bin/env python3
"""真实执行页脚 PHP，验证公安备案图标不会被重复插入。"""
import json
from pathlib import Path
import subprocess
import unittest

项目 = Path(__file__).resolve().parents[1]
PHP = '/www/server/php/80/bin/php'
执行片段 = r'''
define('__TYPECHO_ROOT_DIR__', getcwd());
class 测试选项 {
    public $title = '测试博客';
    public $aurora_lang = 'zh-CN';
    public $aurora_copy = '1';
    public $siteUrl = 'https://blog.example/';
    public $themeUrl = 'https://blog.example/usr/themes/Aurora';
    public $feedUrl = 'https://blog.example/feed/';
    public $aurora_beian;
    public function title() { echo $this->title; }
    public function themeUrl($路径) { echo $this->themeUrl . '/' . $路径; }
}
class Helper {
    public static $选项;
    public static function options() { return self::$选项; }
}
class 测试页面 {
    public $options;
    public function footer() {}
    public function render() { include 'footer.php'; }
}
require 'functions.php';
$数据 = json_decode(stream_get_contents(STDIN), true);
$选项 = new 测试选项();
$选项->aurora_beian = $数据['html'];
Helper::$选项 = $选项;
$页面 = new 测试页面(); $页面->options = $选项; $页面->render();
'''

def 渲染(备案):
    结果 = subprocess.run([PHP, '-r', 执行片段], input=json.dumps({'html': 备案}),
                        text=True, capture_output=True, cwd=项目, check=True)
    return 结果.stdout

class 备案页脚测试(unittest.TestCase):
    def test_existing_logo_is_not_duplicated(self):
        备案 = '<a href="https://beian.mps.gov.cn/#/query/webSearch"><img src="/existing.png" alt="">测试公安备案号</a>'
        输出 = 渲染(备案)
        self.assertEqual(输出.count('<img'), 1)
        self.assertIn(备案, 输出)

    def test_only_official_hosts_receive_logo(self):
        for 域名 in ('beian.mps.gov.cn.evil.example', 'evil.example/?beian.mps.gov.cn'):
            with self.subTest(域名=域名):
                备案 = '<a href="https://' + 域名 + '">普通链接</a>'
                self.assertNotIn('<img', 渲染(备案))

    def test_legacy_official_link_receives_logo(self):
        for 域名 in ('www.beian.gov.cn', 'beian.gov.cn'):
            with self.subTest(域名=域名):
                输出 = 渲染('<a href="http://' + 域名 + '/portal/registerSystemInfo">测试公安备案号</a>')
                self.assertEqual(输出.count('class="police-beian-logo"'), 1)

    def test_current_link_preserves_record_and_attributes(self):
        链接 = '<a href="https://beian.mps.gov.cn/#/query/webSearch" target="_blank" rel="noopener">'
        输出 = 渲染('<a href="https://beian.miit.gov.cn/">测试ICP</a> | ' + 链接 + '测试公安备案号</a>')
        self.assertIn(链接 + '<img', 输出)
        self.assertIn('width="20" height="20"', 输出)
        self.assertIn('https://blog.example/usr/themes/Aurora/assets/ghs.png', 输出)
        self.assertIn('测试公安备案号</a>', 输出)
        self.assertEqual(输出.count('<img'), 1)

    def test_empty_config_does_not_show_filing_row(self):
        self.assertNotIn('class="footer-beian"', 渲染(''))

if __name__ == '__main__':
    unittest.main()
