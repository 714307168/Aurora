#!/usr/bin/env python3
"""Aurora 路线图功能契约测试。"""
from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def text(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


class AuroraRoadmapContractTest(unittest.TestCase):
    def test_homepage_uses_dense_featured_layout(self):
        index = text("index.php")
        sidebar = text("sidebar.php")
        css = text("assets/aurora.css")
        self.assertIn('class="home-featured"', index)
        self.assertIn('class="home-layout', index)
        self.assertIn('class="art-thumb', index)
        self.assertIn("Aurora::content_image", index)
        self.assertIn("Aurora::site_stats", index)
        self.assertIn("Aurora::featured_post", index)
        self.assertIn('class="side-cats compact-grid"', sidebar)
        self.assertIn('class="side-box side-archive-fold"', sidebar)
        self.assertIn("limit=24", sidebar)
        self.assertIn("max-width:1380px", css)
        self.assertIn(".home-layout{display:grid", css)
        self.assertIn(".home-featured{", css)
        self.assertIn("grid-template-columns:minmax(0,1fr) 190px", css)
        self.assertIn(':root[data-theme="light"] .home-featured-copy>p', css)
        self.assertIn("overflow-wrap:anywhere", css)
        self.assertIn("@media (max-width:480px)", css)
        self.assertIn(".topbar-search{display:none}", css)
        self.assertIn("homeFeatured", text("scripts/qa-scan.py"))
        self.assertIn("homeThumbs", text("scripts/qa-scan.py"))

    def test_content_value_features_are_wired(self):
        post = text("post.php")
        functions = text("functions.php")
        sidebar = text("sidebar.php")
        self.assertIn('id="post-reading-meta"', post)
        self.assertIn('class="post-breadcrumb"', post)
        self.assertIn("Aurora::post_views", post)
        self.assertIn("$this->related(4)", post)
        self.assertIn("function post_views", functions)
        self.assertNotIn("ALTER TABLE", functions)
        self.assertIn("->expression('views', 'views + 1', false)", functions)
        self.assertIn("->order('views'", sidebar)

    def test_seo_and_feed_features_are_wired(self):
        header = text("header.php")
        self.assertIn('rel="canonical"', header)
        self.assertIn('application/ld+json', header)
        self.assertIn('property="og:image"', header)
        functions = text("functions.php")
        self.assertIn("($widget->is('post') || $widget->is('page')) ? self::content_image($widget) : null", functions)
        self.assertIn("function content_image", functions)
        self.assertIn('name="twitter:card"', header)
        self.assertIn("$this->options->keywords", header)
        self.assertNotIn("(string)$this->options->description;\n$ogImage", header)
        self.assertIn('rel="alternate"', header)
        self.assertIn("feedUrl", header)

    def test_optional_interaction_features_are_wired(self):
        post = text("post.php")
        comments = text("comments.php")
        functions = text("functions.php")
        js = text("assets/aurora.js")
        self.assertIn('class="post-reactions"', post)
        self.assertIn('data-action="like"', post)
        self.assertIn('data-action="favorite"', post)
        self.assertIn("aurora_comment_provider", functions)
        self.assertIn("function themeConfig(", functions)
        self.assertIn("aurora_sidebar", post)
        self.assertIn("no-sidebar", text("assets/aurora.css"))
        self.assertIn("giscus.app/client.js", comments)
        self.assertIn("Artalk", comments)
        self.assertIn("listComments()", comments)
        self.assertIn("commentsRequireMail", comments)
        self.assertIn("user->hasLogin()", comments)
        self.assertIn("function postReactions", js)

    def test_code_line_count_has_no_off_by_one(self):
        js = text("assets/aurora.js")
        css = text("assets/aurora.css")
        self.assertNotIn('split("\\n").length - 1', js)
        self.assertIn("pre.code-lines{display:grid", css)
        self.assertIn("grid-template-columns:auto minmax(0,1fr)", css)
        self.assertNotIn("pre{display:flex;flex-wrap:wrap", css)

    def test_i18n_and_accessibility_contract(self):
        functions = text("functions.php")
        header = text("header.php")
        js = text("assets/aurora.js")
        css = text("assets/aurora.css")
        self.assertIn("function t(", functions)
        self.assertIn("aurora_lang", functions)
        self.assertIn("Aurora::t('home')", header)
        self.assertIn('aria-pressed', js)
        self.assertIn("var UI =", js)
        self.assertNotIn('btn.textContent = "复制"', js)
        self.assertNotIn("🌙", header + js)
        self.assertNotIn("☀️", header + js)
        self.assertIn('t === "light" ? "☀" : "☾"', js)
        self.assertIn("--text-2:#b8c4d4", css)
        self.assertIn("--text-3:#8795aa", css)
        self.assertIn("input::placeholder", css)

    def test_new_visitors_default_to_dark_theme(self):
        header = text("header.php")
        js = text("assets/aurora.js")
        self.assertIn('data-theme="dark"', header)
        self.assertIn("localStorage.getItem('aurora-theme')", header)
        self.assertIn('saved === "light" || saved === "dark" ? saved : "dark"', js)
        self.assertNotIn("prefers-color-scheme", js)

    def test_release_pipeline_tests_new_code_and_fails_closed(self):
        qa = text("scripts/qa-scan.py")
        release = text("scripts/release.sh")
        self.assertIn("sys.exit(1", qa)
        self.assertIn("location.href", qa)
        self.assertIn("Network.setCacheDisabled", qa)
        self.assertIn("aurora-qa-404-", qa)
        self.assertIn("codeAligned", qa)
        self.assertIn("python3 -m unittest", release)
        self.assertLess(release.index("同步线上站"), release.index("AURORA_SITE=https://www.liuyg.cn python3 scripts/qa-scan.py"))
        self.assertIn("trap rollback ERR", release)
        self.assertNotIn("git commit -m \"$MSG${CHANGED_ASSETS:+（含 assets 改动，版本号已 bump）}\" || echo", release)
        self.assertIn("ElementTree", release)

    def test_sitemap_generator_is_part_of_release(self):
        release = text("scripts/release.sh")
        self.assertTrue((ROOT / "scripts/generate-sitemap.php").exists())
        self.assertIn("generate-sitemap.php", release)

    def test_no_obvious_backdoor_primitives(self):
        merged = "\n".join(
            text(name)
            for name in ["functions.php", "header.php", "post.php", "comments.php", "assets/aurora.js"]
        ).lower()
        self.assertNotIn("eval(", merged)
        self.assertNotIn("base64_decode(", merged)
        self.assertNotIn("shell_exec(", merged)


if __name__ == "__main__":
    unittest.main(verbosity=2)
