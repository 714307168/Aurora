<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<aside class="aurora-sidebar" id="aurora-sidebar">
<?php if($this->is('post')): ?>
    <!-- 文章页侧栏：目录(置顶) + 热门文章 + 标签云 -->
    <div class="side-box side-toc">
        <h4 class="side-title"><i>☰</i> 目录</h4>
        <nav class="toc-wrap" id="post-toc" aria-label="文章目录"></nav>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>♦</i> 热门文章</h4>
        <ol class="side-hot">
        <?php
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $hot = $db->fetchAll($db->select()->from($prefix . 'contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish')
            ->order('commentsNum', Typecho_Db::SORT_DESC)->limit(6));
        foreach ($hot as $p):
        ?>
        <li><a href="<?php echo rtrim($this->options->siteUrl, '/') . '/index.php/archives/' . $p['cid'] . '/'; ?>"><?php echo htmlspecialchars($p['title']); ?></a></li>
        <?php endforeach; ?>
        </ol>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>▣</i> 标签云</h4>
        <div class="side-tags">
            <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&desc=1&ignoreZeroCount=1&limit=36')->to($tags); ?>
            <?php if ($tags->have()): ?>
            <?php while ($tags->next()): ?>
            <?php $sz = 12 + (int)min(6, $tags->count * 0.9); ?>
            <a href="<?php $tags->permalink(); ?>" style="font-size:<?php echo $sz; ?>px"><?php $tags->name(); ?></a>
            <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="side-box">
        <h4 class="side-title"><i>◎</i> 搜索</h4>
        <form class="side-search" method="post" action="<?php echo $this->options->siteUrl; ?>">
            <input type="text" name="s" placeholder="搜索文章…" value="<?php echo htmlspecialchars($this->request->get('s')); ?>">
            <button type="submit" aria-label="搜索">🔍</button>
        </form>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>✦</i> 分类</h4>
        <ul class="side-cats">
            <?php $this->widget('Widget_Metas_Category_List')->parse('<li><a href="{permalink}"><span>{name}</span><em>{count}</em></a></li>'); ?>
        </ul>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>♦</i> 热门文章</h4>
        <ol class="side-hot">
        <?php
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $hot = $db->fetchAll($db->select()->from($prefix . 'contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish')
            ->order('commentsNum', Typecho_Db::SORT_DESC)->limit(6));
        foreach ($hot as $p):
        ?>
        <li><a href="<?php echo rtrim($this->options->siteUrl, '/') . '/index.php/archives/' . $p['cid'] . '/'; ?>"><?php echo htmlspecialchars($p['title']); ?></a></li>
        <?php endforeach; ?>
        </ol>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>▣</i> 标签云</h4>
        <div class="side-tags">
            <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&desc=1&ignoreZeroCount=1&limit=36')->to($tags); ?>
            <?php if ($tags->have()): ?>
            <?php while ($tags->next()): ?>
            <?php $sz = 12 + (int)min(6, $tags->count * 0.9); ?>
            <a href="<?php $tags->permalink(); ?>" style="font-size:<?php echo $sz; ?>px"><?php $tags->name(); ?></a>
            <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="side-box">
        <h4 class="side-title"><i>▣</i> 归档</h4>
        <ul class="side-archive">
            <?php $this->widget('Widget_Contents_Post_Date', 'type=month&format=Y 年 n 月&limit=8')->parse('<li><a href="{permalink}" rel="nofollow">{date}</a></li>'); ?>
        </ul>
    </div>
<?php endif; ?>
</aside>
