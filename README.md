# Aurora — Typecho 科技风博客主题

Aurora 是从零开发的 Typecho 1.3 主题：深浅双色、玻璃拟态、两栏技术博客布局，重点强化阅读体验、SEO 与可维护发布。

- 在线演示：https://www.liuyg.cn
- 当前版本：`1.4.0`
- License：MIT

## 功能

- 深色 / 浅色切换：跟随系统、localStorage 记忆、无障碍状态；
- 技术文章增强：highlight.js、语言识别、复制、行号、长代码折叠；
- 阅读体验：侧栏 TOC + scrollspy、阅读进度、字数/时长、图片懒加载与灯箱；
- 内容增值：真实浏览量、按浏览量热门榜、标签相关文章、面包屑、标签热度；
- SEO：canonical、BlogPosting/BreadcrumbList JSON-LD、OG/Twitter Card、RSS、sitemap；
- 互动：本地喜欢/收藏、可选赞助、可选 Typecho/Giscus/Artalk 评论；
- 国际化：简体中文 / English UI；
- 工程化：功能契约、PHP/JS 检查、Chrome CDP 线上 QA、自动备份/回滚、GitHub 发布。

## 安装

1. 把仓库目录改名为 `Aurora`，上传至 `usr/themes/Aurora/`；
2. Typecho 后台 → 外观 → 启用 Aurora；
3. 外观设置里填写备案号，评论默认保持关闭；
4. 需要 RSS 时使用站点 `feedUrl`；sitemap 通过 `scripts/generate-sitemap.php` 生成。

```text
Aurora/
├── header.php / footer.php
├── index.php / archive.php / post.php / page.php
├── page-links.php / comments.php / sidebar.php / 404.php
├── functions.php / style.css
├── assets/
│   ├── aurora.css
│   ├── aurora.js
│   └── logo.svg
├── tests/test_theme_features.py
├── scripts/
│   ├── qa-scan.py
│   ├── generate-sitemap.php
│   └── release.sh
└── docs/
```

## 配置说明

- 评论：`off`（默认）、`native`、`giscus`、`artalk`；第三方参数不完整时回退关闭；
- 赞助二维码：留空完全不展示；
- 备案：通过 `aurora_beian` 配置，仓库不硬编码站点专属备案；
- 友链：依赖 [Links Plus](https://github.com/lhl77/Typecho-Plugin-LinksPlus)；
- highlight.js 使用 cdnjs 浏览器版，不要替换成 npm 的 Node 构建。

## 开发与发布

```bash
cd /root/aurora-theme
bash scripts/release.sh "feat: 变更说明"
```

脚本会执行契约测试、语法检查、静态资源版本递增、线上备份/同步、sitemap、真实浏览器 QA、Git 提交推送和 HTTP 验证。详细规则见 `docs/09-SOP-发版流程.md`。

## License

MIT © JavaLYG
