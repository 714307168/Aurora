# Aurora — 原创深色科技风 Typecho 博客主题

极光（Aurora）：完全原创的深色科技风 Typecho 主题。玻璃拟态卡片、霓虹青蓝渐变、两栏布局 + 右侧栏、代码块语言识别与一键复制，专注高阅读体验。

用示例：https://www.liuyg.cn

## ✦ 特性

- **深色科技感**：深蓝黑渐变底 + 网格光晕 + 霓虹青蓝（`#22d3ee` → `#3b82f6`）渐变主色
- **玻璃拟态卡片**：文章卡片、侧栏、友链均为半透明玻璃卡片，悬浮发光描边上浮
- **两栏布局**：主内容 + 右侧栏（sticky），侧栏含搜索 / 分类 / 热门文章 / 标签云 / 归档
- **代码块增强**：自动识别编辑语言（Java / Python / C++ / Go / SQL / Shell 等）显示语言标签，一键复制；配合 highlight.js 语法着色
- **高阅读体验**：正文 16.5px / 1.85 行高、标题层次侧边条、代码块深色高亮、表格 / 引用适配深色
- **响应式**：窄屏自动折叠为单栏，侧栏下移
- **原创导航**：顶部 Logo 点击返回首页、移动端折叠菜单、回到顶部按钮

## ⚙️ 安装

1. 把整个 `Aurora` 目录上传到 Typecho 的 `usr/themes/` 下
2. 登录后台 → 控制台 → 外观，启用 **Aurora**
3. （可选）在「外观设置」里开启/关闭右侧栏、页脚版权

```bash
# 线上示例：目录结构
usr/themes/Aurora/
├── style.css          # 主题声明
├── index.php          # 首页（两栏）
├── archive.php        # 分类 / 搜索 / 标签 / 作者页
├── post.php           # 文章页
├── page.php           # 独立页面
├── page-links.php     # 友链页（slug=links 自动匹配）
├── comments.php       # 评论
├── sidebar.php        # 右侧栏
├── 404.php
├── header.php / footer.php
├── functions.php
└── assets/
    ├── aurora.css     # 样式
    ├── aurora.js      # 交互
    └── logo.svg       # logo / favicon
```

## 🔧 说明

- 友链页需要 [Links Plus 插件](https://github.com/lhl77/Typecho-Plugin-LinksPlus) 支持（`page-links.php` 中输出）
- 代码高亮使用 [highlight.js](https://highlightjs.org/)（CDN），语言标签与复制由 `aurora.js` 完成
- 备案号在 `footer.php`，请保留或替换为自己的备案信息
- 模块图标使用 Unicode 符号，无第三方图标库依赖

## 📄 License

MIT © JavaLYG
