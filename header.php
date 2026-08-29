<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $this->archiveTitle(array(
        'category' => _t('%s'),
        'search'   => _t('搜索：%s'),
        'tag'      => _t('标签：%s'),
        'author'   => _t('%s 的文章')
    ), '', ' - '); $this->options->title(); ?></title>
    <link rel="icon" href="<?php $this->options->themeUrl('assets/logo.svg'); ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo $this->options->themeUrl('assets/aurora.css'); ?>?v=20260836">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <meta name="description" content="<?php $this->options->description(); ?>">
    <meta name="keywords" content="<?php $this->options->description(); ?>">
    <meta property="og:site_name" content="<?php $this->options->title(); ?>">
    <meta property="og:type" content="website">
    <?php if ($this->is('post')): ?>
        <meta property="og:title" content="<?php $this->archiveTitle('', ''); ?>">
        <meta property="og:description" content="<?php echo $this->description; ?>">
        <?php if (Aurora::og_image($this)): ?><meta property="og:image" content="<?php echo Aurora::og_image($this); ?>"><?php endif; ?>
        <meta name="keywords" content="<?php echo $this->fields->keywords ? $this->fields->keywords : $this->options->description(); ?>">
    <?php endif; ?>
    <?php $this->header('generator=&template=&pingback=&xmlrpc=&wlw='); ?>
</head>
<body>

<header class="aurora-topbar" id="aurora-top">
    <div class="topbar-inner">
        <a class="aurora-brand" href="<?php echo $this->options->siteUrl; ?>" aria-label="返回首页">
            <svg class="brand-mark" width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="aur-lg" x1="0" y1="0" x2="48" y2="48">
                        <stop offset="0%" stop-color="#22d3ee"/><stop offset="100%" stop-color="#3b82f6"/>
                    </linearGradient>
                </defs>
                <rect x="9" y="9" width="30" height="30" rx="7" stroke="url(#aur-lg)" stroke-width="2.4" fill="rgba(34,211,238,.07)"/>
                <path d="M24 13 L24 35" stroke="url(#aur-lg)" stroke-width="2.4" stroke-linecap="round"/>
                <path d="M24 13 L18 19 M24 13 L30 19 M24 35 L18 29 M24 35 L30 29" stroke="url(#aur-lg)" stroke-width="2.4" stroke-linecap="round"/>
                <circle cx="24" cy="24" r="3.2" fill="url(#aur-lg)"/>
            </svg>
            <span class="brand-name"><?php $this->options->title(); ?></span>
        </a>

        <nav class="aurora-nav" id="aurora-nav">
            <a href="<?php echo $this->options->siteUrl; ?>" class="nav-link">首页</a>
            <?php $this->widget('Widget_Contents_Page_List')->parse('<a href="{permalink}" class="nav-link">{title}</a>'); ?>
            <div class="nav-drop">
                <span class="nav-link">分类 <b class="caret">▾</b></span>
                <div class="drop-menu">
                    <?php $this->widget('Widget_Metas_Category_List')->parse('<a href="{permalink}">{name}</a>'); ?>
                </div>
            </div>
        </nav>

        <button id="theme-toggle" class="theme-toggle" aria-label="切换主题" title="切换亮/暗">🌙</button>

        <button class="nav-toggle" id="nav-toggle" aria-label="菜单"><span></span><span></span><span></span></button>

        <div class="topbar-search" id="top-search">
            <form method="post" action="<?php echo $this->options->siteUrl; ?>">
                <input type="text" name="s" placeholder="搜点什么…" value="<?php echo htmlspecialchars($this->request->get('s')); ?>">
            </form>
        </div>
    </div>
</header>

<main class="aurora-main">
