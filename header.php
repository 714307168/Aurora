<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$isPost = $this->is('post');
$canonical = Aurora::canonical($this);
$metaTitle = $isPost ? (string)$this->title : ((string)$this->archiveTitle ?: (string)$this->options->title);
$metaDescription = $isPost ? trim(strip_tags((string)$this->description)) : (string)$this->options->description;
$metaKeywords = $isPost && $this->fields->keywords ? (string)$this->fields->keywords : (string)$this->options->description;
$ogImage = Aurora::og_image($this);
$jsonLd = Aurora::json_ld($this);
$titleFormats = Aurora::lang() === 'en-US' ? array(
    'category' => '%s', 'search' => 'Search: %s', 'tag' => 'Tag: %s', 'author' => 'Posts by %s'
) : array(
    'category' => '%s', 'search' => '搜索：%s', 'tag' => '标签：%s', 'author' => '%s 的文章'
);
?>
<!DOCTYPE html>
<html lang="<?php echo Aurora::e(Aurora::lang()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $this->archiveTitle($titleFormats, '', ' - '); $this->options->title(); ?></title>
    <link rel="icon" href="<?php $this->options->themeUrl('assets/logo.svg'); ?>" type="image/svg+xml">
    <?php if (!$this->is('single')): ?><link rel="canonical" href="<?php echo Aurora::e($canonical); ?>"><?php endif; ?>
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo Aurora::e($this->options->feedUrl); ?>">
    <link rel="stylesheet" href="<?php echo $this->options->themeUrl('assets/aurora.css'); ?>?v=20260842">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">

    <meta name="description" content="<?php echo Aurora::e($metaDescription); ?>">
    <meta name="keywords" content="<?php echo Aurora::e($metaKeywords); ?>">
    <meta property="og:site_name" content="<?php echo Aurora::e($this->options->title); ?>">
    <meta property="og:type" content="<?php echo $isPost ? 'article' : 'website'; ?>">
    <meta property="og:title" content="<?php echo Aurora::e($metaTitle); ?>">
    <meta property="og:description" content="<?php echo Aurora::e($metaDescription); ?>">
    <meta property="og:url" content="<?php echo Aurora::e($canonical); ?>">
    <meta property="og:image" content="<?php echo Aurora::e($ogImage); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo Aurora::e($metaTitle); ?>">
    <meta name="twitter:description" content="<?php echo Aurora::e($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo Aurora::e($ogImage); ?>">
    <?php if ($jsonLd): ?>
    <script type="application/ld+json"><?php echo $jsonLd; ?></script>
    <?php endif; ?>
    <?php $this->header('description=&keywords=&generator=&template=&pingback=&xmlrpc=&wlw=&rss2=&rss1=&atom=&social='); ?>
</head>
<body>

<header class="aurora-topbar" id="aurora-top">
    <div class="topbar-inner">
        <a class="aurora-brand" href="<?php echo $this->options->siteUrl; ?>" aria-label="<?php echo Aurora::e(Aurora::t('home')); ?>">
            <svg class="brand-mark" width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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

        <nav class="aurora-nav" id="aurora-nav" aria-label="<?php echo Aurora::e(Aurora::t('main_nav')); ?>">
            <a href="<?php echo $this->options->siteUrl; ?>" class="nav-link"><?php echo Aurora::t('home'); ?></a>
            <?php $this->widget('Widget_Contents_Page_List')->parse('<a href="{permalink}" class="nav-link">{title}</a>'); ?>
            <div class="nav-drop">
                <span class="nav-link"><?php echo Aurora::t('categories'); ?> <b class="caret">▾</b></span>
                <div class="drop-menu">
                    <?php $this->widget('Widget_Metas_Category_List')->parse('<a href="{permalink}">{name}</a>'); ?>
                </div>
            </div>
            <a href="<?php echo Aurora::e($this->options->feedUrl); ?>" class="nav-link nav-rss" rel="alternate" title="RSS">RSS</a>
        </nav>

        <button id="theme-toggle" class="theme-toggle" type="button" aria-label="<?php echo Aurora::e(Aurora::t('theme_toggle')); ?>" title="<?php echo Aurora::e(Aurora::t('theme_toggle')); ?>">🌙</button>
        <button class="nav-toggle" id="nav-toggle" type="button" aria-label="<?php echo Aurora::e(Aurora::t('menu')); ?>" aria-expanded="false"><span></span><span></span><span></span></button>

        <div class="topbar-search" id="top-search">
            <form method="post" action="<?php echo $this->options->siteUrl; ?>" role="search">
                <input type="search" name="s" placeholder="<?php echo Aurora::e(Aurora::t('search')); ?>" value="<?php echo Aurora::e($this->request->get('s')); ?>" aria-label="<?php echo Aurora::e(Aurora::t('search')); ?>">
            </form>
        </div>
    </div>
</header>

<main class="aurora-main">
