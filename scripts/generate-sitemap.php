<?php
/** 生成 Typecho sitemap.xml；CLI 用法：php generate-sitemap.php */
$webroot = getenv('AURORA_WEBROOT') ?: '/home/wwwroot/www.liuyg.cn';
$config = rtrim($webroot, '/') . '/config.inc.php';
if (!is_file($config)) {
    fwrite(STDERR, "找不到 Typecho 配置：{$config}\n");
    exit(1);
}
require $config;

$db = Typecho\Db::get();
$option = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'siteUrl'));
$base = rtrim($option && $option['value'] ? $option['value'] : 'https://www.liuyg.cn', '/');
if (strpos($base, 'http://') === 0) $base = 'https://' . substr($base, 7);

$urls = array(array('loc' => $base . '/', 'lastmod' => gmdate('c')));
$posts = $db->fetchAll($db->select('cid', 'modified')->from('table.contents')
    ->where('type = ?', 'post')->where('status = ?', 'publish')
    ->where('(password IS NULL OR password = ?)', '')->order('modified', Typecho\Db::SORT_DESC));
foreach ($posts as $post) {
    $urls[] = array('loc' => $base . '/index.php/archives/' . (int)$post['cid'] . '/', 'lastmod' => gmdate('c', (int)$post['modified']));
}
$pages = $db->fetchAll($db->select('slug', 'modified')->from('table.contents')
    ->where('type = ?', 'page')->where('status = ?', 'publish')
    ->where('(password IS NULL OR password = ?)', '')->order('modified', Typecho\Db::SORT_DESC));
foreach ($pages as $page) {
    $urls[] = array('loc' => $base . '/index.php/' . rawurlencode($page['slug']) . '.html', 'lastmod' => gmdate('c', (int)$page['modified']));
}
$metas = $db->fetchAll($db->select('slug', 'type')->from('table.metas')
    ->where('type IN ?', array('category', 'tag'))->order('mid', Typecho\Db::SORT_ASC));
foreach ($metas as $meta) {
    $urls[] = array('loc' => $base . '/index.php/' . $meta['type'] . '/' . rawurlencode($meta['slug']) . '/', 'lastmod' => null);
}

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $url) {
    $xml .= "  <url><loc>" . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>";
    if ($url['lastmod']) $xml .= "<lastmod>" . $url['lastmod'] . "</lastmod>";
    $xml .= "</url>\n";
}
$xml .= "</urlset>\n";

$target = rtrim($webroot, '/') . '/sitemap.xml';
$tmp = $target . '.tmp.' . getmypid();
if (file_put_contents($tmp, $xml, LOCK_EX) === false || !rename($tmp, $target)) {
    @unlink($tmp);
    fwrite(STDERR, "写入 sitemap 失败：{$target}\n");
    exit(1);
}
@chmod($target, 0644);
echo "已生成 {$target}，共 " . count($urls) . " 条 URL\n";
