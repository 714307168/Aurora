<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="aurora-container">
    <div class="content-pane">
        <section class="err-page">
            <h1 class="err-code">404</h1>
            <p class="err-text">页面走丢了，去别处看看吧。</p>
            <a class="btn-back" href="<?php echo $this->options->siteUrl; ?>">← 回到首页</a>
        </section>
    </div>
</div>

<?php $this->need('footer.php'); ?>
