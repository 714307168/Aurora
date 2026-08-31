<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $showSidebar = $this->options->aurora_sidebar !== '0'; ?>
<?php $homeStats = Aurora::site_stats(); $featured = Aurora::featured_post(); ?>
<?php $this->need('header.php'); ?>

<div class="aurora-container home-page">
    <section class="home-featured">
        <div class="home-featured-copy">
            <span class="hero-kicker">BUILD · LEARN · SHARE</span>
            <h1><?php $this->options->title(); ?></h1>
            <p><?php $this->options->description(); ?>，记录 Java、运维、SSL 与每一次真实折腾。</p>
            <div class="hero-stats" aria-label="站点统计">
                <span><strong><?php echo $homeStats['posts']; ?></strong> 篇文章</span>
                <span><strong><?php echo $homeStats['categories']; ?></strong> 个分类</span>
                <span><strong><?php echo $homeStats['tags']; ?></strong> 个标签</span>
            </div>
            <?php if ($featured): ?>
            <a class="featured-link" href="<?php echo Aurora::e(Aurora::post_url($featured['cid'])); ?>">
                <span>最新发布</span><strong><?php echo Aurora::e($featured['title']); ?></strong><em>阅读全文 →</em>
            </a>
            <?php endif; ?>
        </div>
        <div class="hero-terminal" aria-hidden="true">
            <div class="terminal-bar"><i></i><i></i><i></i><span>javalyg — zsh</span></div>
            <div class="terminal-body">
                <p><b>$</b> whoami</p><p class="terminal-output">JavaLYG</p>
                <p><b>$</b> focus --now</p><p class="terminal-output">Java · SSL · DevOps</p>
                <p><b>$</b> status</p><p class="terminal-ok">● 持续更新中</p>
            </div>
        </div>
    </section>

    <div class="home-layout<?php echo $showSidebar ? '' : ' no-sidebar'; ?>">
        <div class="content-pane">
            <div class="section-heading"><h2>最新文章</h2><a href="<?php $this->options->feedUrl(); ?>">RSS 订阅 ↗</a></div>
            <section class="article-list home-article-list">
            <?php while ($this->next()): ?>
                <?php $cover = Aurora::content_image($this); ?>
                <article class="art-card">
                    <div class="art-content">
                        <h2 class="art-title"><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h2>
                        <p class="art-excerpt"><?php $this->excerpt(72); ?></p>
                        <div class="art-meta">
                            <time datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time>
                            <?php if ($this->category): ?><span class="cat"><?php $this->category(','); ?></span><?php endif; ?>
                            <span class="views"><?php echo Aurora::post_views($this, false) . ' ' . Aurora::t('views'); ?></span>
                        </div>
                    </div>
                    <a class="art-thumb<?php echo $cover ? ' has-image' : ' is-placeholder'; ?>" href="<?php $this->permalink(); ?>" tabindex="-1" aria-hidden="true">
                        <?php if ($cover): ?><img src="<?php echo Aurora::e($cover); ?>" alt="" loading="lazy" decoding="async">
                        <?php else: ?><span>&lt;/&gt;</span><small>TECH NOTE</small><?php endif; ?>
                    </a>
                </article>
            <?php endwhile; ?>
            </section>

            <?php $this->pageNav('‹', '›', 3, '…', array('wrapTag' => 'nav', 'itemTag' => 'span')); ?>
        </div>
        <?php if ($showSidebar): ?><?php $this->need('sidebar.php'); ?><?php endif; ?>
    </div>
</div>

<?php $this->need('footer.php'); ?>
