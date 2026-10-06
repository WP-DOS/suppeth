"""Structural checks; runtime rendering is a separate WordPress smoke test."""
import importlib.util
import json
import os
from pathlib import Path
import re
import subprocess
import tempfile
import unittest
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT
TOKEN = re.compile(r"<!--\s*(/?)wp:([\w/-]+)(.*?)-->", re.DOTALL)


def read_markup(path):
    """Evaluate PHP patterns and resolve references before structural assertions."""
    if path.suffix == ".php":
        source = subprocess.run(
            ["php", str(ROOT / "tests/fixtures/pattern-stubs.php"), str(path)],
            check=True, capture_output=True, text=True,
        ).stdout
    else:
        source = path.read_text()
    def expand(match):
        slug = json.loads(match.group(1))["slug"]
        if not slug.startswith("suppeth/"):
            raise ValueError("Unexpected pattern namespace: " + slug)
        return read_markup(THEME / "patterns" / (slug.split("/")[1] + ".php"))
    source = re.sub(r'<!-- wp:pattern (\{[^\n]+\}) /-->', expand, source)
    # Assert the emitted markup verbatim; normalization can hide parser defects.
    return source

class ThemeTests(unittest.TestCase):
    def test_standalone_theme_identity(self):
        self.assertEqual(THEME, ROOT)
        css = (THEME / "style.css").read_text()
        self.assertIn("Theme Name: Suppeth", css)
        self.assertIn("Text Domain: suppeth", css)
        blueprint = json.loads((ROOT / "tests/blueprint.json").read_text())
        self.assertEqual(blueprint["steps"][0]["themeFolderName"], "suppeth")

    def test_wordpress_minimum_version_is_consistent(self):
        for filename in ["style.css", "readme.txt"]:
            self.assertIn("Requires at least: 7.1", (THEME / filename).read_text())
        config = json.loads((THEME / "theme.json").read_text())
        self.assertEqual(config["$schema"], "https://schemas.wp.org/wp/7.1/theme.json")
        blueprint = json.loads((ROOT / "tests/blueprint.json").read_text())
        self.assertEqual(blueprint["preferredVersions"]["wp"], "7.1")
        development = (ROOT / "docs/development.md").read_text()
        self.assertIn("--wp=7.1", development)

    def test_php_pattern_delimiters_are_canonical_without_normalization(self):
        for path in THEME.glob("patterns/*.php"):
            for translated in [False, True]:
                with self.subTest(pattern=path.name, translated=translated):
                    source = subprocess.run(
                        ["php", str(ROOT / "tests/fixtures/pattern-stubs.php"), str(path)],
                        env={**os.environ, "SUPPETH_TEST_TRANSLATION": "1" if translated else ""},
                        check=True, capture_output=True, text=True,
                    ).stdout
                    for match in TOKEN.finditer(source):
                        if match[1]:
                            continue
                        raw = match[3]
                        standalone = raw.rstrip().endswith("/")
                        attributes = raw.strip().removesuffix("/").strip()
                        expected = "<!-- wp:" + match[2]
                        if attributes:
                            self.assertIsInstance(json.loads(attributes), dict)
                            expected += " " + attributes
                        expected += " /-->" if standalone else " -->"
                        self.assertEqual(match[0], expected)

    def test_translations_and_subdirectory_home_link(self):
        for path in [*THEME.glob("templates/*.html"), *THEME.glob("parts/*.html")]:
            source = path.read_text()
            self.assertNotRegex(source, r">[A-Za-z][^<]*<")
            for match in TOKEN.finditer(source):
                raw = match[3].strip().removesuffix("/").strip()
                if raw:
                    attrs = json.loads(raw)
                    self.assertFalse(set(attrs) & {"label", "moreText", "buttonText", "prefix", "ariaLabel"})
        for path in THEME.glob("patterns/*.php"):
            self.assertNotIn("wp_json_encode", path.read_text())
            translated = subprocess.run(
                ["php", str(ROOT / "tests/fixtures/pattern-stubs.php"), str(path)],
                env={**os.environ, "SUPPETH_TEST_TRANSLATION": "1"},
                check=True, capture_output=True, text=True,
            ).stdout
            for match in TOKEN.finditer(translated):
                raw = match[3].strip().removesuffix("/").strip()
                if raw:
                    attrs = json.loads(raw)
                    for key in ["label", "buttonText", "moreText", "prefix", "ariaLabel"]:
                        if key in attrs:
                            self.assertTrue(attrs[key].startswith('Translated " < > -- & '))
                            self.assertNotIn("--", raw)
                            self.assertNotIn("<", raw)
        not_found = read_markup(THEME / "templates/404.html")
        self.assertIn('href="https://example.test/subdirectory/"', not_found)
        self.assertNotIn('href="/"', not_found)

    def test_form_contrast_and_release_metadata(self):
        config = json.loads((THEME / "theme.json").read_text())
        palette = {item["slug"]: item["color"] for item in config["settings"]["color"]["palette"]}
        def luminance(color):
            rgb = [int(color[i:i+2], 16) / 255 for i in [1, 3, 5]]
            rgb = [v / 12.92 if v <= 0.04045 else ((v + 0.055) / 1.055) ** 2.4 for v in rgb]
            return sum(v * weight for v, weight in zip(rgb, [0.2126, 0.7152, 0.0722]))
        light, dark = sorted([luminance(palette["base"]), luminance(palette["control-border"])], reverse=True)
        self.assertGreaterEqual((light + 0.05) / (dark + 0.05), 3)
        css = (THEME / "style.css").read_text()
        self.assertIn("border: 1px solid var(--wp--preset--color--control-border)", css)
        readme = (THEME / "readme.txt").read_text()
        self.assertEqual(re.search(r"^Version: (.+)$", css, re.M)[1],
                         re.search(r"^Version: (.+)$", readme, re.M)[1])
        php = (THEME / "functions.php").read_text()
        self.assertIn('<div class="suppeth-image-frame">', php)
        self.assertNotIn('<span class="suppeth-image-frame">', php)

    def test_settings_and_local_fonts(self):
        config = json.loads((THEME / "theme.json").read_text())
        self.assertEqual(config["version"], 3)
        self.assertTrue(config["settings"]["appearanceTools"])
        for font in config["settings"]["typography"]["fontFamilies"]:
            for face in font.get("fontFace", []):
                self.assertEqual(face["fontDisplay"], "swap")
                for src in face["src"]:
                    self.assertTrue(src.startswith("file:./assets/fonts/"))
                    self.assertTrue((THEME / src.removeprefix("file:./")).is_file())
        self.assertEqual(config["styles"]["typography"]["fontFamily"], "var:preset|font-family|dm-sans")
        self.assertEqual(config["styles"]["elements"]["heading"]["typography"]["fontFamily"], "var:preset|font-family|manrope")
        self.assertEqual({p["name"] for p in config["templateParts"]}, {"header", "footer", "sidebar"})

    def test_sidebar_template_registration_and_layout(self):
        config = json.loads((THEME / "theme.json").read_text())
        templates = {item["name"]: item for item in config["customTemplates"]}
        expected = {"page-sidebar": ["page"], "single-sidebar": ["post"],
                    "index-sidebar": [], "archive-sidebar": []}
        self.assertEqual(set(templates), set(expected))
        sidebar = next(part for part in config["templateParts"] if part["name"] == "sidebar")
        self.assertEqual(sidebar["area"], "uncategorized")
        for name, post_types in expected.items():
            self.assertEqual(templates[name]["postTypes"], post_types)
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertEqual(source.count("<main "), 1)
            self.assertIn('"tagName":"header"', source)
            self.assertIn('"tagName":"footer"', source)
            self.assertIn('"align":"wide","className":"suppeth-sidebar-layout"', source)
            self.assertIn('"width":"66.66%"', source)
            self.assertIn('"width":"33.33%"', source)
            self.assertNotIn('"isStackedOnMobile":false', source)
            self.assertEqual(source.count('"slug":"sidebar","tagName":"aside"'), 1)
            self.assertNotIn('"slug":"sidebar"', (THEME / "templates" / (name.split("-")[0] + ".html")).read_text())
        for name in ["page-sidebar", "single-sidebar"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertIn('"contentSize":"100%","wideSize":"100%"', source)
        for name in ["index-sidebar", "archive-sidebar"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertIn('"query":{"inherit":true}', source)
            self.assertIn("wp:query-pagination", source)
            self.assertIn("wp:query-no-results", source)
        source = read_markup(THEME / "templates/single-sidebar.html")
        for block in ["post-author", "comment-template", "post-comments-form", "post-navigation-link"]:
            self.assertIn("wp:" + block, source)

    def test_shared_sidebar_uses_dynamic_blocks(self):
        source = read_markup(THEME / "parts/sidebar.html")
        self.assertIn("wp:categories", source)
        self.assertIn('wp:latest-posts {"postsToShow":5,"displayPostDate":true', source)
        self.assertEqual(source.count("<h2 "), 2)
        self.assertNotIn("wp:post-content", source)
        self.assertNotIn('href=', source)

    def test_header_patterns_are_discoverable_and_use_native_blocks(self):
        paths = list((THEME / "patterns").glob("header-*.php"))
        self.assertEqual({p.stem for p in paths},
                         {"header-search", "header-centered", "header-two-row", "header-sticky"})
        for path in paths:
            source = path.read_text()
            self.assertIn("Slug: suppeth/" + path.stem, source)
            self.assertIn("Categories: header", source)
            self.assertIn("Block Types: core/template-part/header", source)
            self.assertIn('wp:site-title {"level":0', source)
            self.assertIn("wp:site-logo", source)
            self.assertIn('"overlayMenu":"mobile"', source)
            self.assertNotIn('"ref":', source)
            self.assertNotIn("wp:navigation-link", source)
        search_pattern = (THEME / "patterns/header-search.php").read_text()
        self.assertIn('alignfull suppeth-header-with-search ', search_pattern)
        self.assertNotIn('alignfull suppeth-header-search ', search_pattern)
        for name in ["header-search", "header-two-row"]:
            source = (THEME / "patterns" / (name + ".php")).read_text()
            self.assertIn("wp:search", source)
            self.assertIn("serialize_block_attributes", source)
            self.assertIn("'suppeth'", source)
        css = (THEME / "style.css").read_text()
        self.assertIn("header.wp-block-template-part:has(.suppeth-header-sticky)", css)
        self.assertIn("inset-block-start: 32px", css)
        self.assertIn("inset-block-start: 46px", css)
        self.assertNotIn("suppeth-header-sticky", (THEME / "parts/header.html").read_text())

    def test_footer_patterns_are_discoverable_and_use_native_blocks(self):
        paths = list((THEME / "patterns").glob("footer-*.php"))
        self.assertEqual({p.stem for p in paths},
                         {"footer-compact", "footer-centered", "footer-columns", "footer-search"})
        for path in paths:
            source = path.read_text()
            self.assertIn("Title:", source)
            self.assertIn("Slug: suppeth/" + path.stem, source)
            self.assertIn("Categories: footer", source)
            self.assertIn("Block Types: core/template-part/footer", source)
            self.assertIn('wp:site-title {"level":0', source)
            self.assertIn("wp:navigation", source)
            self.assertIn('"overlayMenu":"never"', read_markup(path))
            self.assertIn('"ariaLabel":"Footer"', read_markup(path))
            self.assertIn("esc_html_e( 'Made with WordPress.', 'suppeth' )", source)
            self.assertIn("suppeth-footer", source)
            for forbidden in ['"ref":', "wp:navigation-link", 'href=', '"lock":', '<footer']:
                self.assertNotIn(forbidden, source)
        centered = (THEME / "patterns/footer-centered.php").read_text()
        self.assertIn("wp:site-logo", centered)
        self.assertIn('"justifyContent":"center"', read_markup(THEME / "patterns/footer-centered.php"))
        columns = (THEME / "patterns/footer-columns.php").read_text()
        self.assertEqual(columns.count("<!-- wp:column -->"), 3)
        self.assertIn('wp:latest-posts {"postsToShow":3', columns)
        self.assertNotIn('"isStackedOnMobile":false', columns)
        search = (THEME / "patterns/footer-search.php").read_text()
        self.assertIn('"showLabel":true', read_markup(THEME / "patterns/footer-search.php"))
        self.assertIn('"buttonText":"Search"', read_markup(THEME / "patterns/footer-search.php"))
        self.assertIn("serialize_block_attributes", search)
        self.assertIn('alignwide suppeth-footer suppeth-footer-with-search', search)
        default = read_markup(THEME / "parts/footer.html")
        self.assertNotIn("wp:pattern", default)
        self.assertNotIn("wp:search", default)

    def test_full_width_content_and_alt_disclosure_contract(self):
        for name in ["page", "single"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertIn('"align":"full","layout":{"type":"constrained"}', source)
        php = (THEME / "functions.php").read_text()
        self.assertIn("WP_HTML_Tag_Processor", php)
        self.assertIn("esc_html( $alt )", php)
        self.assertIn("suppeth-image-description", php)
        self.assertIn("render_block_core/image", php)
        fixtures = (ROOT / "tests/fixtures/blocks.html").read_text()
        self.assertIn('wp-block-cover alignfull', fixtures)
        self.assertIn('alt=""', fixtures)
        self.assertIn("Long image description", fixtures)

    def test_regression_fixture_coverage(self):
        blocks = (ROOT / "tests/fixtures/blocks.html").read_text()
        for name in ["image", "cover", "gallery", "columns", "media-text", "group", "list", "quote", "code", "buttons", "table", "details", "search"]:
            self.assertIn("wp:" + name, blocks)
        self.assertGreaterEqual(blocks.count("wp:separator -->"), 16)
        typography = (ROOT / "tests/fixtures/typography.html").read_text()
        for level in range(1, 7):
            self.assertIn("<h" + str(level) + " ", typography)
        for tag in ["strong", "em", "mark", "s", "code", "sup", "sub", "small"]:
            self.assertIn("<" + tag, typography)
        blueprint = json.loads((ROOT / "tests/blueprint.json").read_text())
        for step in blueprint["steps"]:
            resource = step.get("data", {})
            if isinstance(resource, dict) and resource.get("resource") == "bundled":
                self.assertTrue((ROOT / "tests" / resource["path"]).is_file())

    def test_editable_block_visual_defaults(self):
        config = json.loads((THEME / "theme.json").read_text())
        blocks = config["styles"]["blocks"]
        self.assertEqual(blocks["core/post-title"]["elements"]["link"]["typography"]["textDecoration"], "none")
        self.assertEqual(blocks["core/heading"]["spacing"]["margin"]["top"], "clamp(2.5rem, 5vw, 4rem)")
        self.assertEqual(blocks["core/quote"]["border"]["left"]["width"], "2px")
        self.assertEqual(blocks["core/quote"]["border"]["left"]["color"], "var:preset|color|border")
        self.assertEqual(blocks["core/code"]["color"]["background"], "var:preset|color|surface")
        self.assertEqual(blocks["core/code"]["border"]["width"], "1px")
        for name in ["core/columns", "core/media-text"]:
            self.assertEqual(blocks[name]["spacing"]["margin"]["bottom"], "clamp(2.5rem, 5vw, 4rem)")
        self.assertEqual(blocks["core/columns"]["spacing"]["blockGap"], "2.5rem clamp(2rem, 4vw, 3rem)")
        for name in ["core/image", "core/post-featured-image"]:
            self.assertEqual(blocks[name]["border"]["radius"], "6px")
        button = config["styles"]["elements"]["button"]
        self.assertEqual(button["border"]["radius"], "6px")
        outline = blocks["core/button"]["variations"]["outline"]
        for property_name in ["spacing", "border", "typography"]:
            self.assertEqual(button[property_name], outline[property_name])

    def test_post_details_and_discussion(self):
        source = read_markup(THEME / "templates/single.html")
        for block in ['post-terms {"term":"category"', 'post-terms {"term":"post_tag"',
                      'post-author {"showBio":true', 'comment-template',
                      'comment-author-name', 'comment-date', 'comment-content',
                      'comment-reply-link', 'post-comments-form']:
            self.assertIn("wp:" + block, source)
        fixture = (ROOT / "tests/fixtures/seed.php").read_text()
        self.assertIn("'comment_parent'", fixture)
        self.assertIn("wp_set_post_terms", fixture)
        self.assertIn("'description'", fixture)

    def test_adjacent_article_navigation(self):
        for name in ["single", "single-sidebar"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertIn('<nav class="wp-block-group suppeth-post-navigation">', source)
            for direction in ["previous", "next"]:
                attrs = next(json.loads(match.group(3).strip().removesuffix("/").strip())
                             for match in TOKEN.finditer(source)
                             if match.group(2) == "post-navigation-link"
                             and json.loads(match.group(3).strip().removesuffix("/").strip())["type"] == direction)
                self.assertTrue(attrs["showTitle"])
                self.assertTrue(attrs["linkLabel"])
                self.assertIn("article", attrs["label"])
        css = (THEME / "style.css").read_text()
        self.assertIn("grid-template-columns: repeat(2, minmax(0, 1fr))", css)
        self.assertIn(".suppeth-post-navigation a:hover .post-navigation-link__title", css)
        self.assertIn(".suppeth-post-navigation > .wp-block-post-navigation-link:not(:has(a))", css)

    def test_dynamic_article_summaries(self):
        for name in ["index", "archive", "search"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            for block in ["post-featured-image", "post-author-name", "post-date", "post-terms", "post-excerpt"]:
                self.assertIn("wp:" + block, source)
            self.assertIn("suppeth-post-summary", source)
            self.assertNotIn("Alex Morgan", source)
            self.assertNotIn("wp:lock", source)
            self.assertIn('"paginationArrow":"arrow"', source)

    def test_editorial_preview_is_local_and_has_explicit_menus(self):
        fixture = (ROOT / "tests/fixtures/site.php").read_text()
        self.assertIn("restricted", (ROOT / "tests/fixtures/seed.php").read_text())
        self.assertIn("'127.0.0.1', 'localhost'", fixture)
        for slug in ['about', 'now', 'resources', 'contact', 'journal', 'services', 'portfolio']:
            self.assertIn("'" + slug + "' => array(", fixture)
        for option in ['page_on_front', 'page_for_posts', 'show_on_front']:
            self.assertIn("update_option('" + option, fixture)
        self.assertIn("wp:navigation-link", fixture)
        self.assertIn("wp_set_post_categories", fixture)
        self.assertIn("post_date_gmt", fixture)
        self.assertIn("<!-- wp:more --><!--more--><!-- /wp:more -->", fixture)
        footer = read_markup(THEME / "parts/footer.html")
        self.assertIn('"ariaLabel":"Footer"', footer)
        self.assertIn("wp:site-tagline", footer)
        self.assertNotIn("The Quiet Edit", footer)

    def test_template_showcases_are_preview_only_and_reusable(self):
        fixture = (ROOT / "tests/fixtures/template-showcase.php").read_text()
        for slug in ["templates", "template-page", "template-page-sidebar", "template-post-sidebar", "template-index-sidebar"]:
            self.assertIn("'" + slug + "'", fixture)
        for slug in ["page-template-index-sidebar", "category-template-showcase"]:
            self.assertIn("'" + slug + "'", fixture)
        self.assertIn("'_wp_page_template', 'page-sidebar'", fixture)
        self.assertIn("'_wp_page_template', 'single-sidebar'", fixture)
        self.assertIn("get_template_directory() . '/templates/index-sidebar.html'", fixture)
        self.assertIn("get_template_directory() . '/templates/archive-sidebar.html'", fixture)
        self.assertIn("serialize_blocks(resolve_pattern_blocks(parse_blocks($source)))", fixture)
        self.assertIn("'core/navigation-submenu'", fixture)
        self.assertIn("'label' => 'Templates'", fixture)
        self.assertIn("array_filter(parse_blocks($menu->post_content)", fixture)
        self.assertIn("'127.0.0.1', 'localhost'", fixture)
        self.assertNotIn("wp_delete_post", fixture)
        blueprint = json.loads((ROOT / "tests/blueprint.json").read_text())
        scripts = [step.get("code", "") for step in blueprint["steps"] if step["step"] == "runPHP"]
        self.assertLess(next(i for i, code in enumerate(scripts) if "suppeth-seed.php" in code),
                        next(i for i, code in enumerate(scripts) if "suppeth-template-showcase.php" in code))

    def test_shared_form_layout_and_disabled_states(self):
        css = (THEME / "style.css").read_text()
        self.assertIn('.wp-block-post-comments-form :where(input:not([type="submit"]):not([type="checkbox"]), textarea)', css)
        self.assertIn(".wp-block-post-comments-form label,\n.suppeth-demo-contact label", css)
        self.assertIn(".wp-block-post-comments-form .comment-form-cookies-consent label {\n\tdisplay: inline;", css)
        self.assertIn(".wp-element-button:disabled,", css)
        self.assertIn("opacity: 0.5", css)

    def test_independent_proof_pages(self):
        seed = (ROOT / "tests/fixtures/seed.php").read_text()
        for slug in ["elements-proof", "typography-proof", "block-style-test", "typography-test"]:
            self.assertIn("$upsert('page', '" + slug + "'", seed)
        elements = (ROOT / "tests/fixtures/elements-proof.html").read_text()
        self.assertIn("/typography-proof/", elements)
        self.assertGreaterEqual(elements.count("wp:details"), 5)
        self.assertIn('"showContent":true', elements)
        self.assertIn("[suppeth_demo_contact]", elements)
        self.assertIn(" disabled", elements)
        typography = (ROOT / "tests/fixtures/typography-proof.html").read_text()
        self.assertIn("/elements-proof/", typography)
        for level in range(1, 7):
            self.assertIn("<h" + str(level) + " ", typography)
        self.assertIn("has-manrope-font-family", typography)
        self.assertIn("DM Sans", typography)
        self.assertIn("Spacing and rhythm", typography)
        # Proofs aren't inserted into either editorial menu.
        site = (ROOT / "tests/fixtures/site.php").read_text()
        self.assertNotIn("elements-proof", site)
        self.assertNotIn("typography-proof", site)

    def test_showcase_pages_and_safe_demo_form(self):
        services = (ROOT / "tests/fixtures/services.html").read_text()
        portfolio = (ROOT / "tests/fixtures/portfolio.html").read_text()
        contact = (ROOT / "tests/fixtures/contact.html").read_text()
        for block in ["columns", "buttons", "details", "group"]:
            self.assertIn("wp:" + block, services)
        for block in ["image", "columns", "media-text", "buttons"]:
            self.assertIn("wp:" + block, portfolio)
        self.assertIn("[suppeth_demo_contact]", contact)
        php = (ROOT / "tests/fixtures/demo-contact.php").read_text()
        self.assertIn("'127.0.0.1', 'localhost'", php)
        self.assertNotIn("wp_mail", php)
        script = (ROOT / "tests/fixtures/demo-contact.js").read_text()
        self.assertIn("event.preventDefault()", script)
        self.assertIn("form.reportValidity()", script)
        self.assertNotIn("fetch(", script)
        self.assertNotIn("localStorage", script)

    def test_templates_are_balanced_with_valid_attributes(self):
        paths = [*THEME.glob("templates/*.html"), *THEME.glob("parts/*.html"),
                 *(ROOT / "tests/fixtures").glob("*.html"), *THEME.glob("patterns/*.php")]
        for path in paths:
            with self.subTest(path=path.name):
                source = read_markup(path)
                stack = []
                for match in TOKEN.finditer(source):
                    closing, name, raw = match.groups()
                    raw = raw.strip()
                    if closing:
                        self.assertTrue(stack, f"Unexpected closing block: {name}")
                        self.assertEqual(stack.pop(), name)
                    else:
                        standalone = raw.endswith("/")
                        if standalone:
                            raw = raw[:-1].strip()
                        if raw:
                            self.assertIsInstance(json.loads(raw), dict)
                        if not standalone:
                            stack.append(name)
                self.assertFalse(stack, f"Unclosed blocks: {stack}")

    def test_required_templates_and_landmarks(self):
        for name in ["index", "page", "single", "archive", "search", "404"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertEqual(source.count("<main "), 1)
            self.assertIn('"tagName":"header"', source)
            self.assertIn('"tagName":"footer"', source)
        self.assertIn('"level":0', (THEME / "parts/header.html").read_text())
        for name in ["index", "archive", "search"]:
            source = read_markup(THEME / "templates" / (name + ".html"))
            self.assertIn("wp:query-no-results", source)
            self.assertIn("wp:query-pagination", source)

    def test_only_scoped_enhancement_assets(self):
        self.assertEqual({p.relative_to(THEME).as_posix() for p in (THEME / "assets").rglob("*.js")}, {"assets/image-descriptions.js", "assets/details.js"})
        self.assertEqual({p.name for p in THEME.rglob("*.woff2")}, {"dm-sans-latin.woff2", "manrope-latin.woff2", "jetbrains-mono-latin.woff2"})
        for name in ["DM-Sans-OFL.txt", "Manrope-OFL.txt", "JetBrains-Mono-OFL.txt"]:
            self.assertIn("SIL OPEN FONT LICENSE", (THEME / "assets/fonts" / name).read_text())
        php = (THEME / "functions.php").read_text()
        self.assertIn("'suppeth-image-descriptions'", php)
        self.assertNotIn("render_block_core/media-text", php)
        self.assertIn("add_editor_style", php)
        self.assertIn("render_block_core/details", php)
        self.assertIn("'suppeth-details'", php)
        details_js = (THEME / "assets/details.js").read_text()
        self.assertIn("prefers-reduced-motion: reduce", details_js)
        self.assertIn("details.wp-block-details", details_js)
        self.assertIn("body.inert", details_js)
        self.assertIn("previous.cancel()", details_js)
        self.assertNotIn("suppeth-navigation-indicator", php)
        css = (THEME / "style.css").read_text()
        self.assertIn('[aria-current="page"] {\n\tfont-weight: 600;', css)
        self.assertNotIn("suppeth-navigation-dot", css)
        self.assertIn(":where(pre, code, kbd, samp)", css)
        self.assertIn("font-family: var(--wp--preset--font-family--mono)", css)
        self.assertIn("@media (prefers-reduced-motion: no-preference)", css)
        self.assertIn("scroll-behavior: smooth", css)
        for path in [*THEME.glob("templates/*.html"), *THEME.glob("parts/*.html")]:
            self.assertNotIn("<script", path.read_text())
            self.assertNotIn('src="https://', path.read_text())

    def test_zip_root_contents_and_reproducibility(self):
        spec = importlib.util.spec_from_file_location("package_theme", ROOT / "scripts/package-theme.py")
        module = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(module)
        with tempfile.TemporaryDirectory() as directory:
            first = module.package(Path(directory) / "first.zip")
            second = module.package(Path(directory) / "second.zip")
            self.assertEqual(first.read_bytes(), second.read_bytes())
            with ZipFile(first) as archive:
                names = archive.namelist()
                self.assertIn("suppeth/style.css", names)
                self.assertIn("suppeth/theme.json", names)
                self.assertTrue(all(n.startswith("suppeth/") for n in names))
                self.assertFalse(any(".git" in n or ".env" in n for n in names))
                expected = [p for directory in ["assets", "parts", "patterns", "templates"]
                            for p in (THEME / directory).rglob("*") if p.is_file()]
                expected += [THEME / name for name in ["style.css", "theme.json", "functions.php", "readme.txt", "LICENSE"]]
                self.assertEqual(set(names), {"suppeth/" + p.relative_to(THEME).as_posix() for p in expected})
                for excluded in ["tests/", "scripts/", "docs/", "vendor/", "dist/", "AGENTS.md", "CONTEXT.md", "composer.json", "README.md"]:
                    self.assertFalse(any(n.startswith("suppeth/" + excluded) for n in names))

if __name__ == "__main__":
    unittest.main()
