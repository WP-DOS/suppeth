# Develop and validate Suppeth

## Prerequisites

Use PHP, Composer, Python 3.10+, and Node.js 24+ (required by the editor browser runner; CI uses Node 24). Theme runtime requires no build or Composer dependencies.

From this repository root:

```sh
composer install
composer lint
find patterns -name '*.php' -exec php -l {} \;
php -l functions.php
node --check assets/image-descriptions.js
node --check assets/details.js
python3 -m unittest discover -s tests -v
python3 scripts/package-theme.py
```

The ZIP is dist/suppeth.zip. Packaging uses an explicit runtime allowlist; docs, tests, fixtures, credentials and dependencies remain outside it. CI publishes the suppeth-theme artifact.

## Fixed disposable preview

tests/blueprint.json is the reusable fixture blueprint, pinned to WordPress 7.1 / PHP 8.3. From the repository root:

```sh
npx @wp-playground/cli@3.1.57 server --wp=7.1 --php=8.3 --define WP_DEVELOPMENT_MODE theme --mount=.:/wordpress/wp-content/themes/suppeth --blueprint=tests/blueprint.json --blueprint-may-read-adjacent-files
```

The sandbox is disposable. Stop and recreate it to reset content and saved template overrides. Never run fixture seeders against a real website. Blueprint login credentials are public test-only values, not production secrets.

The fixtures cover long content, pagination, navigation, images, comments, typography, core blocks and empty/disabled controls. A **Templates** submenu links to eight default/sidebar comparisons; start at /templates/. New routes are /template-page/, /template-page-sidebar/, /template-post-sidebar/, /template-index-sidebar/, and /category/template-showcase/. Page and Post examples use native template assignments. Index and Archive examples use route-scoped, preview-only saved templates sourced from the theme files, with pattern references expanded before saving to preserve query/pagination context; the normal Journal and other category archives stay unchanged. The showcase seeder updates its own content and replaces its own submenu entry when rerun. The Quiet Edit is a fictional test site with /journal/, /services/, /portfolio/ and /contact/. Independent proofs live at /elements-proof/ and /typography-proof/; legacy checks use /block-style-test/ and /typography-test/. The local contact helper never sends or stores messages.

## Runtime checks

```sh
python3 scripts/check-runtime.py
python3 scripts/check-runtime.py --php 7.4
python3 scripts/check-runtime.py --preview
```

The runner uses Playground CLI 3.1.57 and requires an explicit PHP success receipt, not merely a successful CLI exit. Structural tests use PHP stand-ins and inspect emitted delimiters without normalization; runtime checks cover real registration, rendering, translation and parser round trips. CI also runs tests/pattern-validation.browser.js against the real Site Editor parser, checking all registered patterns, retained attributes and editor serialization round trips. Other browser scripts in tests/*.browser.js remain manual checks. With a running preview, for example:

```sh
npx agent-browser --session suppeth-styles open http://127.0.0.1:9400/elements-proof/
npx agent-browser --session suppeth-styles eval --stdin < tests/block-styles.browser.js
```

For the editor-parser regression suite, with the disposable preview running:

```sh
python3 scripts/check-editor.py
```

The runner uses agent-browser 0.38.2, signs in with the public admin/password blueprint account when auto-login is unavailable, and closes only its own browser session. CI supplies --server-log to wait for Playground's explicit Ready message: HTTP may respond before blueprint seeding and login finish. Theme-supplied patterns are read-only in the library; see [header replacement](theme-reference.md#header-patterns).

On /templates/, run tests/template-showcase.browser.js to check every submenu destination, layout and post query. Run tests/sidebar-layout.browser.js on each sidebar example at desktop and mobile widths.

Repeat relevant checks at mobile widths and with keyboard/reduced-motion settings. See [theme reference](theme-reference.md) for scenario-specific scripts.

## Compatibility evidence

Existing metadata declares WordPress 7.1+ and PHP 7.4+; the blueprint targets WordPress 7.1 / PHP 8.3. Runtime standards checks passed on WordPress 7.1.2 with PHP 8.3.33 and 7.4.33. This is not evidence of a tested full version matrix. Preserve metadata until an explicit compatibility change is validated; record actual outcomes in [verification](verification.md).

## Site adoption

Merge a theme PR into main first. Then update site/themes/suppeth to the merged commit through a site PR into develop. Release through develop → main; dashboard changes and live deployment require separate approval.

See the site's [development guide](https://github.com/WP-DOS/site/blob/develop/docs/development.md). A site checkout is optional for theme work.
