<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php if ($this->allow('comment')): ?>
<section class="comment-block" id="comments">
    <h3 class="block-title"><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></h3>
    <div class="comment-list">
        <?php $this->comments()->to($comments); ?>
        <div class="comment-single"><p class="comment-content"><?php $comments->content(); ?></p></div>
    </div>

    <div class="comment-form" id="<?php $this->respondId(); ?>">
        <form action="<?php $this->commentUrl(); ?>" method="post">
            <fieldset>
                <input type="text" name="author" placeholder="昵称 *" value="<?php $this->remember('author'); ?>" required>
                <input type="email" name="mail" placeholder="邮箱 *" value="<?php $this->remember('mail'); ?>" required>
                <input type="url" name="url" placeholder="https://" value="<?php $this->remember('url'); ?>">
            </fieldset>
            <textarea name="text" rows="4" placeholder="留下你的想法…" required><?php $this->remember('text'); ?></textarea>
            <button type="submit" class="btn-primary">发表评论</button>
        </form>
    </div>
</section>
<?php else: ?>
<section class="comment-block" id="comments">
    <p class="comments-off">🔕 评论已关闭</p>
</section>
<?php endif; ?>
