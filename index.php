<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="aurora-container layout">
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
                    <span class="cmt"><?php $this->commentsNum('0 评论', '1 评论', '%d 评论'); ?></span>
                </div>
            </article>
        <?php endwhile; ?>
        </section>

        <?php $this->pageNav('‹', '›', 3, '…', array('wrapTag' => 'nav', 'itemTag' => 'span')); ?>
    </div>

    <aside class="aurora-sidebar">
        <?php $this->need('sidebar.php'); ?>
    </aside>
</div>

<?php $this->need('footer.php'); ?>
