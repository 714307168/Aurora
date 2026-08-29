<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="aurora-container">
    <div class="content-pane">
        <article class="aurora-post">
            <header class="post-head">
                <h1 class="post-title"><?php $this->title(); ?></h1>
                <div class="post-meta"><time datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time></div>
            </header>
            <div class="post-body">
                <style>
                    .aurora-links { list-style:none; margin:24px 0; padding:0; display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; }
                    .aurora-links li { list-style:none; margin:0; padding:22px 12px; background:var(--card-bg); border:1px solid var(--card-border); border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; backdrop-filter:blur(8px); transition:box-shadow .2s,border-color .2s,transform .2s; }
                    .aurora-links li:hover { box-shadow:0 8px 24px rgba(0,0,0,.35),0 0 18px rgba(34,211,238,.15); border-color:var(--aurora); transform:translateY(-3px); }
                    .aurora-links a { display:flex; flex-direction:column; align-items:center; gap:10px; text-decoration:none; width:100%; color:var(--text-2); }
                    .aurora-links img { width:40px; height:40px; border-radius:8px; object-fit:contain; filter:drop-shadow(0 0 6px rgba(34,211,238,.25)); }
                    .aurora-links span { font-size:14.5px; color:var(--text); }
                </style>
                <?php $this->content(); ?>
                <ul class="aurora-links">
                <?php
                if (class_exists('Links_Plugin')) {
                    echo Links_Plugin::output('SHOW_IMG', 0, null, 32);
                }
                ?>
                </ul>
            </div>
            <?php $this->need('comments.php'); ?>
        </article>
    </div>
</div>

<?php $this->need('footer.php'); ?>
