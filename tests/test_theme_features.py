#!/usr/bin/env python3
"""Aurora 路线图功能契约测试。"""
from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def text(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


class AuroraRoadmapContractTest(unittest.TestCase):
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
        self.assertIn("$text = ($widget->is('post') || $widget->is('page')) ?", text("functions.php"))
        self.assertIn('name="twitter:card"', header)
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
        self.assertNotIn('split("\\n").length - 1', js)

    def test_i18n_and_accessibility_contract(self):
        functions = text("functions.php")
        header = text("header.php")
        js = text("assets/aurora.js")
        self.assertIn("function t(", functions)
        self.assertIn("aurora_lang", functions)
        self.assertIn("Aurora::t('home')", header)
        self.assertIn('aria-pressed', js)
        self.assertIn("var UI =", js)
        self.assertNotIn('btn.textContent = "复制"', js)

    def test_release_pipeline_tests_new_code_and_fails_closed(self):
        qa = text("scripts/qa-scan.py")
        release = text("scripts/release.sh")
        self.assertIn("sys.exit(1", qa)
        self.assertIn("location.href", qa)
        self.assertIn("Network.setCacheDisabled", qa)
        self.assertIn("aurora-qa-404-", qa)
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
