<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $showSidebar = $this->options->aurora_sidebar !== '0'; ?>
<?php $this->need('header.php'); ?>

<div class="aurora-container layout<?php echo $showSidebar ? '' : ' no-sidebar'; ?>">
    <div class="content-pane">
        <section class="home-hero">
            <h1><?php $this->options->title(); ?></h1>
            <p><?php $this->options->description(); ?></p>
        </section>

        <section class="article-list">
        <?php while ($this->next()): ?>
            <article class="art-card">
                <h2 class="art-title">
                    <a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a>
                </h2>
                <p class="art-excerpt"><?php $this->excerpt(90); ?></p>
                <div class="art-meta">
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time>
                    <?php if ($this->category): ?><span class="cat"><?php $this->category(','); ?></span><?php endif; ?>
                    <span class="views"><?php echo Aurora::post_views($this, false) . ' ' . Aurora::t('views'); ?></span>
                </div>
            </article>
        <?php endwhile; ?>
        </section>

        <?php $this->pageNav('‹', '›', 3, '…', array('wrapTag' => 'nav', 'itemTag' => 'span')); ?>
    </div>

    <?php if ($showSidebar): ?><aside class="aurora-sidebar">
        <?php $this->need('sidebar.php'); ?>
    </aside><?php endif; ?>
</div>

<?php $this->need('footer.php'); ?>
