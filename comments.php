<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $provider = $this->options->aurora_comment_provider ?: 'off'; ?>

<?php if ($provider === 'giscus' && $this->options->aurora_giscus_repo && $this->options->aurora_giscus_repo_id && $this->options->aurora_giscus_category_id): ?>
<section class="comment-block third-party-comments" id="comments" aria-label="Giscus <?php echo Aurora::e(Aurora::t('comment')); ?>">
    <script src="https://giscus.app/client.js"
        data-repo="<?php echo Aurora::e($this->options->aurora_giscus_repo); ?>"
        data-repo-id="<?php echo Aurora::e($this->options->aurora_giscus_repo_id); ?>"
        data-category="<?php echo Aurora::e($this->options->aurora_giscus_category ?: 'Announcements'); ?>"
        data-category-id="<?php echo Aurora::e($this->options->aurora_giscus_category_id); ?>"
        data-mapping="pathname" data-strict="0" data-reactions-enabled="1"
        data-emit-metadata="0" data-input-position="top" data-theme="preferred_color_scheme"
        data-lang="<?php echo Aurora::lang() === 'en-US' ? 'en' : 'zh-CN'; ?>"
        crossorigin="anonymous" async></script>
</section>

<?php elseif ($provider === 'artalk' && $this->options->aurora_artalk_server && $this->options->aurora_artalk_site): ?>
<section class="comment-block third-party-comments" id="comments" aria-label="Artalk <?php echo Aurora::e(Aurora::t('comment')); ?>">
    <div id="aurora-artalk"></div>
    <link href="https://unpkg.com/artalk@2.10.0/dist/Artalk.css" rel="stylesheet">
    <script src="https://unpkg.com/artalk@2.10.0/dist/Artalk.js"></script>
    <script>
    if (window.Artalk) {
        Artalk.init({
            el: '#aurora-artalk',
            pageKey: <?php echo json_encode((string)$this->permalink, JSON_HEX_TAG | JSON_HEX_AMP); ?>,
            pageTitle: <?php echo json_encode((string)$this->title, JSON_HEX_TAG | JSON_HEX_AMP); ?>,
            server: <?php echo json_encode(rtrim((string)$this->options->aurora_artalk_server, '/'), JSON_HEX_TAG | JSON_HEX_AMP); ?>,
            site: <?php echo json_encode((string)$this->options->aurora_artalk_site, JSON_HEX_TAG | JSON_HEX_AMP); ?>
        });
    }
    </script>
</section>

<?php elseif ($provider === 'native' && $this->allow('comment')): ?>
<section class="comment-block" id="comments">
    <h3 class="block-title"><?php $this->commentsNum(Aurora::t('no_comments'), Aurora::t('one_comment'), Aurora::t('many_comments')); ?></h3>
    <?php $this->comments()->to($comments); ?>
    <?php if ($comments->have()): ?>
        <?php $comments->listComments(); ?>
        <?php $comments->pageNav(Aurora::t('prev_page'), Aurora::t('next_page')); ?>
    <?php endif; ?>

    <div class="comment-form" id="<?php $this->respondId(); ?>">
        <div class="cancel-comment-reply"><?php $comments->cancelReply(); ?></div>
        <h3><?php echo Aurora::t('your_comment'); ?></h3>
        <form action="<?php $this->commentUrl(); ?>" method="post" id="comment-form">
            <textarea name="text" rows="4" placeholder="<?php echo Aurora::e(Aurora::t('comment_content')); ?>" required></textarea>
            <?php if ($this->user->hasLogin()): ?>
            <p class="comment-login">
                <?php echo Aurora::t('signed_in_as'); ?><a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
                · <a href="<?php $this->options->logoutUrl(); ?>"><?php echo Aurora::t('logout'); ?></a>
            </p>
            <?php else: ?>
            <fieldset>
                <input type="text" name="author" placeholder="<?php echo Aurora::e(Aurora::t('name')); ?> *" value="<?php $this->remember('author'); ?>" required>
                <input type="email" name="mail" placeholder="<?php echo Aurora::e(Aurora::t('email')); ?><?php echo $this->options->commentsRequireMail ? ' *' : ' (' . Aurora::e(Aurora::t('optional')) . ')'; ?>" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                <input type="url" name="url" placeholder="<?php echo Aurora::e(Aurora::t('website')); ?><?php echo $this->options->commentsRequireUrl ? ' *' : ' (' . Aurora::e(Aurora::t('optional')) . ')'; ?>" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>>
            </fieldset>
            <?php endif; ?>
            <button type="submit" class="btn-primary"><?php echo Aurora::t('submit_comment'); ?></button>
        </form>
    </div>
</section>

<?php else: ?>
<section class="comment-block" id="comments">
    <p class="comments-off">🔕 <?php echo Aurora::t('comments_off'); ?></p>
</section>
<?php endif; ?>
