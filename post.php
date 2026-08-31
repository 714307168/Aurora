<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$commentProvider = $this->options->aurora_comment_provider ?: 'off';
$postViews = Aurora::post_views($this);
$showSidebar = $this->options->aurora_sidebar !== '0';
?>
<?php $this->need('header.php'); ?>

<div id="read-progress" aria-hidden="true"></div>

<div class="aurora-container layout<?php echo $showSidebar ? '' : ' no-sidebar'; ?>">
    <div class="content-pane">
        <article class="aurora-post" data-post-id="<?php echo (int)$this->cid; ?>">
            <nav class="post-breadcrumb" aria-label="<?php echo Aurora::e(Aurora::t('breadcrumb')); ?>">
                <a href="<?php echo $this->options->siteUrl; ?>"><?php echo Aurora::t('home'); ?></a>
                <span aria-hidden="true">›</span>
                <?php if ($this->category): ?><?php $this->category(' / '); ?><span aria-hidden="true">›</span><?php endif; ?>
                <span aria-current="page"><?php $this->title(); ?></span>
            </nav>

            <header class="post-head">
                <h1 class="post-title"><?php $this->title(); ?></h1>
                <div class="post-meta">
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time>
                    <?php if ($this->category): ?><span class="cat"><?php $this->category(','); ?></span><?php endif; ?>
                    <span class="views"><?php echo $postViews . ' ' . Aurora::t('views'); ?></span>
                    <span id="post-reading-meta" data-template="<?php echo Aurora::e(Aurora::t('reading')); ?>"></span>
                    <?php if ($commentProvider === 'native'): ?><span class="cmt"><?php $this->commentsNum('0 ' . Aurora::t('comment'), '1 ' . Aurora::t('comment'), '%d ' . Aurora::t('comment')); ?></span><?php endif; ?>
                </div>
            </header>

            <div class="post-body">
                <?php $this->content(); ?>
            </div>

            <?php if ($this->tags): ?>
            <div class="post-tags">
                <?php $this->tags('', true, ''); ?>
            </div>
            <?php endif; ?>

            <div class="post-reactions" data-post-id="<?php echo (int)$this->cid; ?>">
                <button type="button" data-action="like" aria-pressed="false">♡ <span><?php echo Aurora::t('like'); ?></span></button>
                <button type="button" data-action="favorite" aria-pressed="false">☆ <span><?php echo Aurora::t('favorite'); ?></span></button>
            </div>

            <?php if ($this->options->aurora_reward_image): ?>
            <details class="post-reward">
                <summary><?php echo Aurora::t('reward'); ?></summary>
                <img src="<?php echo Aurora::e(Aurora::absolute_url($this->options->aurora_reward_image)); ?>" loading="lazy" decoding="async" alt="<?php echo Aurora::e(Aurora::t('reward')); ?>">
            </details>
            <?php endif; ?>

            <?php $this->related(4)->to($related); ?>
            <?php if ($related->have()): ?>
            <section class="related-posts" aria-labelledby="related-title">
                <h2 id="related-title"><?php echo Aurora::t('related'); ?></h2>
                <div class="related-grid">
                    <?php while ($related->next()): ?>
                    <a href="<?php $related->permalink(); ?>">
                        <strong><?php $related->title(); ?></strong>
                        <time datetime="<?php $related->date('c'); ?>"><?php $related->date('Y-m-d'); ?></time>
                    </a>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>

            <nav class="post-nav" aria-label="<?php echo Aurora::e(Aurora::t('post_nav')); ?>">
                <span class="prev"><?php $this->thePrev('‹ %s', Aurora::t('no_more')); ?></span>
                <span class="next"><?php $this->theNext('%s ›', Aurora::t('no_more')); ?></span>
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

    <?php if ($showSidebar): ?><aside class="aurora-sidebar">
        <?php $this->need('sidebar.php'); ?>
    </aside><?php endif; ?>
</div>

<?php $this->need('footer.php'); ?>
