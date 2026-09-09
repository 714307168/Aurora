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
        <?php if($this->options->aurora_beian): ?>
        <?php
        // 只装饰官方公安备案链接；不改备案号、跳转地址或后台保存值。
        $图标地址 = rtrim((string)$this->options->themeUrl, '/') . '/assets/ghs.png';
        $图标 = '<img class="police-beian-logo" src="' . Aurora::e($图标地址) . '" alt="公安备案图标" width="20" height="20">';
        $beian = preg_replace_callback('~(<a\b[^>]*>)(.*?)(</a\s*>)~is', function ($匹配) use ($图标) {
            if (!preg_match('~\shref\s*=\s*([\x22\x27])(.*?)\1~is', $匹配[1], $属性)) return $匹配[0];
            $主机 = strtolower((string)parse_url(html_entity_decode($属性[2], ENT_QUOTES, 'UTF-8'), PHP_URL_HOST));
            if (!in_array($主机, array('beian.mps.gov.cn', 'www.beian.gov.cn', 'beian.gov.cn'), true)) return $匹配[0];
            // 管理员已提供图标则保留，不再追加第二张。
            if (preg_match('/<img\b/i', $匹配[2])) return $匹配[0];
            return $匹配[1] . $图标 . ' ' . $匹配[2] . $匹配[3];
        }, (string)$this->options->aurora_beian);
        ?>
        <p class="footer-beian"><?php echo $beian; ?></p>
        <?php endif; ?>
    </div>
</footer>

<button class="to-top" id="to-top" type="button" aria-label="<?php echo Aurora::e(Aurora::t('back_top')); ?>">↑</button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>if (window.hljs) hljs.highlightAll();</script>
<script src="<?php $this->options->themeUrl('assets/aurora.js'); ?>?v=20260917"></script>
<?php $this->footer(); ?>

</body>
</html>
