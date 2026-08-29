<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="aurora-container">
    <div class="content-pane">
        <article class="aurora-post">
            <header class="post-head">
                <h1 class="post-title"><?php $this->title(); ?></h1>
                <div class="post-meta">
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time>
                </div>
            </header>
            <div class="post-body">
                <?php $this->content(); ?>
            </div>
            <?php $this->need('comments.php'); ?>
        </article>
    </div>
</div>

<?php $this->need('footer.php'); ?>
