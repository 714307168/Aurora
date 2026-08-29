<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
</main>

<footer class="aurora-footer">
    <div class="footer-inner">
        <p class="footer-brand"><?php $this->options->title(); ?> <span class="dot">·</span> 技术分享，从热爱开始</p>
        <p class="footer-meta">
            © <?php echo date('Y'); ?> <a href="<?php $this->options->siteUrl(); ?>" rel="noopener"><?php $this->options->title(); ?></a>. Theme <a href="https://github.com/714307168/Aurora" target="_blank" rel="noopener">Aurora</a>, Made with <span class="heart">♥</span>.
        </p>
        <p class="footer-beian"><?php if($this->options->aurora_beian): ?><?php echo $this->options->aurora_beian; ?><?php endif; ?></p>
    </div>
</footer>

<button class="to-top" id="to-top" aria-label="回到顶部">↑</button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
<script src="<?php $this->options->themeUrl('assets/aurora.js'); ?>?v=20260829"></script>
<?php $this->footer(); ?>

</body>
</html>
