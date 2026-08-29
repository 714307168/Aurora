<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div id="read-progress" aria-hidden="true"></div>

<div class="aurora-container layout">
    <div class="content-pane">
        <article class="aurora-post">
            <header class="post-head">
                <h1 class="post-title"><?php $this->title(); ?></h1>
                <div class="post-meta">
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time>
                    <?php if ($this->category): ?><span class="cat"><?php $this->category(','); ?></span><?php endif; ?>
                    <span class="cmt"><?php $this->commentsNum('0 评论', '1 评论', '%d 评论'); ?></span>
                </div>
            </header>

            <nav class="post-toc" id="post-toc" aria-label="文章目录"></nav>

            <div class="post-body">
                <?php $this->content(); ?>
            </div>

            <?php if ($this->tags): ?>
            <div class="post-tags">
                <?php $this->tags('', true, ''); ?>
            </div>
            <?php endif; ?>

            <nav class="post-nav">
                <span class="prev"><?php $this->thePrev('‹ %s', '没有了'); ?></span>
                <span class="next"><?php $this->theNext('%s ›', '没有了'); ?></span>
            </nav>

            <?php if ($this->options->authorTitle): ?>
            <section class="post-author">
                <div class="author-ava"><?php $this->author->gravatar(90); ?></div>
                <div class="author-info">
                    <b><?php $this->author(); ?></b>
                    <span><?php $this->options->authorTitle(); ?></span>
                </div>
            </section>
            <?php endif; ?>

            <?php $this->need('comments.php'); ?>
        </article>
    </div>

    <aside class="aurora-sidebar">
        <?php $this->need('sidebar.php'); ?>
    </aside>
</div>

<?php $this->need('footer.php'); ?>
