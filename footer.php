<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
</main>

<footer class="aurora-footer">
    <div class="footer-inner">
        <p class="footer-brand"><?php $this->options->title(); ?> <span class="dot">·</span> <?php echo Aurora::t('footer_tagline'); ?></p>
        <?php if ($this->options->aurora_copy !== '0'): ?>
        <p class="footer-meta">
            © <?php echo date('Y'); ?> <a href="<?php echo $this->options->siteUrl; ?>" rel="noopener"><?php $this->options->title(); ?></a>.
            Theme <a href="https://github.com/714307168/Aurora" target="_blank" rel="noopener">Aurora</a>, Made with <span class="heart">♥</span>.
            <a href="<?php echo Aurora::e($this->options->feedUrl); ?>" rel="alternate">RSS</a>
        </p>
        <?php endif; ?>
        <p class="footer-beian"><?php if($this->options->aurora_beian): ?><?php echo $this->options->aurora_beian; ?><?php endif; ?></p>
    </div>
</footer>

<button class="to-top" id="to-top" type="button" aria-label="<?php echo Aurora::e(Aurora::t('back_top')); ?>">↑</button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>if (window.hljs) hljs.highlightAll();</script>
<script src="<?php $this->options->themeUrl('assets/aurora.js'); ?>?v=20260901"></script>
<?php $this->footer(); ?>

</body>
</html>
