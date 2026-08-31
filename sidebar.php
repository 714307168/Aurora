<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$renderHotPosts = function () {
    $db = Typecho_Db::get();
    try {
        $posts = $db->fetchAll($db->select()->from('table.contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish')
            ->order('views', Typecho_Db::SORT_DESC)->order('created', Typecho_Db::SORT_DESC)->limit(6));
    } catch (Exception $error) {
        $posts = $db->fetchAll($db->select()->from('table.contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish')
            ->order('commentsNum', Typecho_Db::SORT_DESC)->limit(6));
    }
    foreach ($posts as $post) {
        echo '<li><a href="' . Aurora::e(Aurora::post_url($post['cid'])) . '">' . Aurora::e($post['title']) . '</a></li>';
    }
};
?>

<aside class="aurora-sidebar" id="aurora-sidebar">
<?php if($this->is('post')): ?>
    <div class="side-box side-toc">
        <h4 class="side-title"><i>☰</i> <?php echo Aurora::t('toc'); ?></h4>
        <nav class="toc-wrap" id="post-toc" aria-label="<?php echo Aurora::e(Aurora::t('toc')); ?>"></nav>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>♦</i> <?php echo Aurora::t('hot'); ?></h4>
        <ol class="side-hot"><?php $renderHotPosts(); ?></ol>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>▣</i> <?php echo Aurora::t('tags'); ?></h4>
        <div class="side-tags">
            <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&desc=1&ignoreZeroCount=1&limit=36')->to($tags); ?>
            <?php if ($tags->have()): while ($tags->next()): ?>
            <?php $size = 12 + (int)min(6, $tags->count * 0.9); $heat = min(1, 0.45 + $tags->count * 0.07); ?>
            <a href="<?php $tags->permalink(); ?>" style="font-size:<?php echo $size; ?>px;--tag-heat:<?php echo $heat; ?>"><?php $tags->name(); ?></a>
            <?php endwhile; endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="side-box">
        <h4 class="side-title"><i>◎</i> <?php echo Aurora::t('search'); ?></h4>
        <form class="side-search" method="post" action="<?php echo $this->options->siteUrl; ?>" role="search">
            <input type="search" name="s" placeholder="<?php echo Aurora::e(Aurora::t('search')); ?>" value="<?php echo Aurora::e($this->request->get('s')); ?>">
        </form>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>✦</i> <?php echo Aurora::t('categories'); ?></h4>
        <ul class="side-cats compact-grid">
            <?php $this->widget('Widget_Metas_Category_List')->parse('<li><a href="{permalink}" title="{name}"><span>{name}</span><em>{count}</em></a></li>'); ?>
        </ul>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>♦</i> <?php echo Aurora::t('hot'); ?></h4>
        <ol class="side-hot"><?php $renderHotPosts(); ?></ol>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>▣</i> <?php echo Aurora::t('tags'); ?></h4>
        <div class="side-tags">
            <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&desc=1&ignoreZeroCount=1&limit=24')->to($tags2); ?>
            <?php if ($tags2->have()): while ($tags2->next()): ?>
            <?php $size = 12 + (int)min(6, $tags2->count * 0.9); $heat = min(1, 0.45 + $tags2->count * 0.07); ?>
            <a href="<?php $tags2->permalink(); ?>" style="font-size:<?php echo $size; ?>px;--tag-heat:<?php echo $heat; ?>"><?php $tags2->name(); ?></a>
            <?php endwhile; endif; ?>
        </div>
    </div>

    <details class="side-box side-archive-fold">
        <summary class="side-title"><i>▣</i> <?php echo Aurora::t('archive'); ?><b>展开</b></summary>
        <ul class="side-archive">
            <?php $archiveFormat = Aurora::lang() === 'en-US' ? 'F Y' : 'Y 年 n 月'; ?>
            <?php $this->widget('Widget_Contents_Post_Date', 'type=month&format=' . urlencode($archiveFormat) . '&limit=12')->parse('<li><a href="{permalink}" rel="nofollow">{date}</a></li>'); ?>
        </ul>
    </details>
<?php endif; ?>
</aside>
