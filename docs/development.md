# Develop and validate Suppeth

## Prerequisites

Use PHP, Composer, Node.js 24.18+, npm 11.16+, and Docker with a running daemon. `.nvmrc` pins the Node version used in CI. Development and runtime tests use `wp-env`; Python and Playground are no longer required. Theme runtime requires no build, npm or Composer dependencies.

From this repository root:

```sh
nvm use
npm ci
composer install
npm run lint
find patterns -name '*.php' -exec php -l {} \;
php -l functions.php
node --check assets/image-descriptions.js
node --check assets/details.js
npm run env:setup
npm test
npm run package
```

The ZIP is dist/suppeth.zip. Packaging uses an explicit runtime allowlist; docs, tests, fixtures, credentials and dependencies remain outside it. CI publishes the suppeth-theme artifact.

## Linting

`npm run lint` runs WordPress Stylelint rules, PHPCS with WordPress/PHPCompatibilityWP rules, and theme validation. PHP compatibility targets the declared PHP 7.4+ minimum and WordPress 7.1+. `lint:theme` runs `bin/validate-theme.mjs`, adapted from [Ipsum's validator](https://github.com/WordPress/ipsum/blob/trunk/bin/validate-theme.mjs). It checks theme/style JSON schemas, pattern headers and references, block nesting and attributes, untranslated text, and editor leftovers. It inspects PHP source without executing it; the runtime and editor tests cover PHP-generated markup and translations. Schema validation requires access to `schemas.wp.org`; it fails rather than skipping when unavailable. Real WordPress and editor-parser checks remain separate.

Individual commands:

```sh
npm run lint:css
npm run lint:php
npm run lint:theme
npm run lint:css:fix
npm run lint:php:fix
npm run lint:staged
```

Fix commands modify source files; review the diff and rerun tests. The specificity-order rule is disabled because it compares unrelated core-block, editor and component contexts; the other WordPress Stylelint rules remain enabled. Staged linting is available on demand; no Git hooks are installed automatically. CI runs lint, three Node packaging/safety checks, and the WordPress runtime/editor regressions through the same Docker setup. Node/Composer dependencies and local environment configuration are excluded from the theme ZIP.

The npm lockfile overrides `qs` to a patched version. `npm audit` still reports upstream development-tool advisories, including critical `simple-git` advisories and denial-of-service advisories in `braces` and `sprintf-js`. The patched major version of `simple-git` is incompatible with wp-env 11.16.0, so it is not forced into the dependency tree. The checked-in environment uses a pinned WordPress ZIP rather than arbitrary Git sources; use only trusted local configuration and sources. Check `npm audit` when updating tools, and review available fixes rather than applying `npm audit fix --force` blindly. These packages are not distributed with the theme.

## Docker preview with wp-env

Start Docker, then run:

```sh
npm run env:setup
npm run env:status
npm run env:stop
```

The development preview is at <http://localhost:8899>. `env:setup` starts WordPress 7.1.2 / PHP 8.3, activates Suppeth, and seeds the synthetic Quiet Edit site described below. `env:start` starts or resumes it without changing the active theme. The public, local-only wp-env login is `admin` / `password`. Debug logging and theme development mode are enabled; debug output stays off the frontend. The theme is mounted live, with `node_modules` hidden from the container.

`npm run env:seed` reapplies the sample content without restarting Docker. It updates fixture-owned pages, posts, menus, and saved Header/Footer/showcase templates, plus Reading settings and the site title; it is for this disposable local site, not a way to preserve manual fixture customizations. The seeder rejects non-local sites, requires Suppeth and WP-CLI, and uses the fixture manifest in `tests/fixtures/manifest.json`. Images, comments and showcase menus are reused on subsequent runs. The local contact helper is installed as a development-only mu-plugin and never sends or stores messages. Optional `npm run env:theme-check` installs and activates Theme Check locally. `npm run env:reset` resets this local database and its saved Site Editor customizations; do not use it when you want to keep them. Local overrides belong in ignored `.wp-env.override.json`. See the [wp-env documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/).

## Source conventions

PHP and JavaScript source files use file-level docblocks with `@package Suppeth` and `@since Suppeth <version>`. Keep WordPress stylesheet metadata in `style.css`. JSON and block-only HTML files remain in their native formats.

Name patterns with `Inserter: no` as `hidden-*.php`; insertable patterns use unprefixed filenames. Every HTML template references its complete `hidden-<template>.php` pattern. Keep labels, search forms, pagination, and recovery messages inline within complete template patterns rather than registering small helper patterns. Separate patterns represent complete templates or shared header, footer, and sidebar designs. The default footer and sidebar retain their existing registered slugs. Resolve patterns by their `Slug` metadata, not by filename.

## Sample content

The Docker preview is disposable. Use `env:reset` followed by `env:setup` to reset content and saved template overrides. Never run fixture seeders against a real website. The local login credentials are public test-only values, not production secrets.

The fixtures cover long content, pagination, navigation, images, comments, typography, core blocks and empty/disabled controls. A **Templates** submenu links to eight default/sidebar comparisons; start at /templates/. New routes are /template-page/, /template-page-sidebar/, /template-post-sidebar/, /template-index-sidebar/, and /category/template-showcase/. Page and Post examples use native template assignments. Index and Archive examples use route-scoped, preview-only saved templates sourced from the theme files, with pattern references expanded before saving to preserve query/pagination context; the normal Journal and other category archives stay unchanged. The showcase seeder updates its own content and replaces its own submenu entry when rerun. The Quiet Edit is a fictional test site with /journal/, /services/, /portfolio/ and /contact/. Independent proofs live at /elements-proof/ and /typography-proof/; legacy checks use /block-style-test/ and /typography-test/. The local contact helper never sends or stores messages.

## Runtime checks

```sh
npm run test:unit
npm run test:runtime
npm run test:editor
npm run env:check
```

The compact PHP suite in `tests/fixtures/standards.php` runs through wp-env's WP-CLI and covers real registration, rendering, translations and parser round trips, subdirectory links, template-part discovery, and image-description escaping. Node tests verify reproducible runtime-only ZIPs and local-only test guards. `bin/check-editor.mjs` runs `tests/pattern-validation.browser.js` against the real Site Editor parser, checking all registered patterns, retained attributes and editor serialization round trips. It requires an explicit success result. These checks retain regressions static lint cannot catch; they do not assert exact layout strings or fixed implementation details. Other browser scripts in tests/*.browser.js remain manual checks. With a running preview, for example:

```sh
npx agent-browser --session suppeth-styles open http://localhost:8899/elements-proof/
npx agent-browser --session suppeth-styles eval --stdin < tests/block-styles.browser.js
```

For the editor-parser regression suite against the running Docker preview:

```sh
npm run test:editor
npm run env:check
```

The runner uses agent-browser 0.38.2, signs in with the public wp-env admin/password account, and closes only its own browser session. CI completes `env:setup` before running the checks. Theme-supplied patterns are read-only in the library; see [header replacement](theme-reference.md#header-patterns).

On /templates/, run tests/template-showcase.browser.js to check every submenu destination, layout and post query. Run tests/sidebar-layout.browser.js on each sidebar example at desktop and mobile widths.

Repeat relevant checks at mobile widths and with keyboard/reduced-motion settings. See [theme reference](theme-reference.md) for scenario-specific scripts.

## Compatibility evidence

Existing metadata declares WordPress 7.1+ and PHP 7.4+; wp-env targets WordPress 7.1.2 / PHP 8.3. Runtime standards checks passed on WordPress 7.1.2 with PHP 8.3.33 and 7.4.33. This is not evidence of a tested full version matrix. Preserve metadata until an explicit compatibility change is validated; record actual outcomes in [verification](verification.md).

## Site adoption

Merge a theme PR into main first. Then update site/themes/suppeth to the merged commit through a site PR into develop. Release through develop → main; dashboard changes and live deployment require separate approval.

See the site's [development guide](https://github.com/WP-DOS/site/blob/develop/docs/development.md). A site checkout is optional for theme work.
