# Setup verification

Current commands and tooling are documented in [development](development.md). Historical sections retain the results from the retired Python/Playground harness.

## Toolchain consolidation (0.1.7)

- WordPress CSS/PHP lint and the adapted Ipsum theme validator passed.
- All three Node packaging/safety tests passed, including byte-identical ZIPs across different time zones and rejection of hosted-site editor checks.
- The PHP runtime suite passed against WordPress 7.1.2 in wp-env, covering PHP-generated and translated patterns, native template-part discovery, rendered templates, subdirectory links and image-description escaping.
- The actual Site Editor parser validated all 20 patterns, including all eight replacement designs, with valid serialization round trips.
- Shared fixtures seeded successfully in Docker; the preview check confirmed the showcase routes, template assignments and repeat-seeding menu behavior.
- Node packaging produced `dist/suppeth.zip` using the runtime allowlist. Contributor tools, fixtures and dependencies remain excluded.
- Python tests/runners and Playground configuration were removed. CI now uses the same npm/Composer/wp-env commands as local development; GitHub Actions execution itself has not been verified locally.

## Serif defaults (0.1.8)

- CSS/PHP/theme lint, all four Node checks, native WordPress runtime checks, all 20 editor patterns, fixture checks and packaging passed.
- Browser inspection at 1440 × 1000 and 390 × 844 confirmed Source Serif 4 body text, Cormorant Garamond headings, the Paper background, loaded local font faces and no horizontal overflow.
- Default contrast on Paper: Ink 11.25:1, secondary ink 5.69:1, links 6.54:1, control borders 4.23:1. These ratios cover the default palette, not arbitrary user-selected combinations.
- Layout spacing and native controls remain unchanged. Existing sans-serif presets remain available; the new defaults do not reset saved Global Styles.

## Heading scale and independent style presets

- The Typography fixture reproduced the lower-heading problem: H5 and H6 fell back to browser sizes of about 15 and 12 px, below the 18 px body text.
- All six levels now have explicit sizes. Browser checks passed at 1280 px and 390 px, with a descending scale, readable lower headings, and no horizontal overflow.
- All four typography/palette combinations passed browser checks at 1280 px and 390 px using WordPress-generated stylesheets: expected body/heading/site-title fonts, palette background, descending heading sizes, and no horizontal overflow.
- The Site Editor displayed Serif/Sans typesets and Paper/Neutral palettes. Selecting Sans followed by Neutral preserved DM Sans body text, Manrope headings, and the neutral palette in the edited state. These inspection changes were not saved.
- All five Node tests passed, including contrast checks for both palettes, independent preset composition, and packaged variation files. WordPress 7.1.2 runtime checks verified native preset discovery; theme schema lint, all 20 editor patterns, and packaging also passed.

## Editable appearance defaults

- The distributed stylesheet dropped from 739 to 249 lines. Editorial class-specific decoration and orphaned homepage styling were removed; ordinary layout and appearance use native block attributes and theme.json settings. Remaining stylesheet rules cover layout/accessibility guards and the image-description enhancement.
- Details marker/wrapper styles and comment-form internals now use block-scoped css in theme.json. Details typography inherits native block settings; consecutive blocks no longer force shared corners or borders. The local contact form loads its own fixture-only stylesheet.
- CSS/PHP/theme lint, six Node checks, WordPress runtime checks (including native sticky-position rendering), editor round-trips for all 20 patterns, repeat seeding, and packaging passed.
- Desktop/mobile Details interaction checks passed. A reduced-motion regression exposed WordPress dropping the nested media query from block-scoped CSS; the icon now uses a duration variable controlled by a stylesheet-level accessibility media query. The corrected normal/reduced-motion checks passed at desktop width, and reduced-motion checks also passed at 390 px. The Contact page loaded its fixture-only stylesheet successfully. Mobile article navigation, image-description layouts, homepage listings, and all ten template destinations also passed.
- Browser tests using WordPress-generated user styles verified that Details accepted a solid 4 px blue border, 32 px padding, system-sans typography, and weight 700 at 1280 px and 390 px. Image corners accepted a zero-radius override. These temporary styles were not saved to the preview database.

## Native blank theme and separate image plugin

- The default now uses system typography and a neutral palette; Serif/Sans and Paper/Neutral remain independent optional presets. Theme appearance uses native controls. style.css retains four usability guards (long-content wrapping, preformatted scrolling, anchor spacing, and template-part margins); theme.json has no custom css fields, and functions.php loads translations and the shared frontend/editor stylesheet only.
- Custom Details behavior and image-description assets/hooks were removed. Native Details and gallery-caption checks passed without any image plugin active. Author details now use separate native Avatar, Author Name, and Biography blocks, with editable avatar corners. Pagination uses native previous/next blocks; the unsupported sticky-header part was retired.
- CSS/PHP/theme lint, six Node checks, WordPress 7.1.2 runtime checks, and editor round-trips for 19 patterns / seven replacement designs passed. Packaging excludes plugins and fixtures.
- The initial metadata-only stylesheet exposed native overflow on unbroken content. After clarification, a minimal explicit stylesheet restores usability guards without decorative overrides. The simple showcase uses descriptive labels; legacy proof fixtures retain stress cases for regression checks.
- Image Descriptions was extracted to a separate local Git repository, with its own package allowlist, WordPress sandbox, CI workflow, and tests. Suppeth does not import, install, activate, or reference it as a dependency; site adoption and GitHub publication remain separate steps.
- The plugin passed standalone packaging/runtime checks on WordPress 7.1.2 with Twenty Twenty-Five. Desktop/mobile/reduced-motion browser checks passed, and a wide gallery exercised two eligible controls without relocating native captions. Its Node development tools have upstream npm audit advisories and remain outside the runtime ZIP.

## Runtime compatibility

scripts/check-runtime.py produced explicit successful PHP receipts using Playground CLI 3.1.57:

| WordPress | PHP | Result |
| --- | --- | --- |
| 7.1.2 | 8.3.33 | Runtime standards checks passed |
| 7.1.2 | 7.4.33 | Runtime standards checks passed |

These checks cover registration, translated block attributes, image-description escaping, subdirectory links, CSS generation and rendered templates. They support the existing PHP minimum for these scenarios; they do not prove every future WordPress/PHP combination.

## Migration checks

- Theme: all 23 structural/packaging tests passed; ZIP contents and reproducibility checked.
- Composer: strict metadata validation and WordPress PHP coding standards passed; the lock refresh changed no dependency versions.
- PHP/JavaScript syntax checks passed for runtime files and migrated fixtures/checks.
- Site: all 4 integration tests passed, including unsafe archive rejection and preservation of bundled font provenance documentation.
- Artifact: site/dist/wpcom assembled successfully with --theme ../suppeth; only packaged runtime theme files are included.
- Local documentation links and whitespace checks passed, except the explicitly pending site submodule pointer to the theme's new AGENTS.md.
- Fixed preview: scripts/check-runtime.py --preview passed on WordPress 7.1.2 / PHP 8.3.33, confirming theme activation and the expected synthetic pages, posts and comments.

## Pattern delimiter fix (0.1.6)

The PHP-generated block comments now use canonical delimiters. Structural checks inspect emitted markup verbatim rather than normalizing it first.

- Before the fix, the delimiter regression failed in 24 plain/translated cases, and the actual editor parser reported invalid blocks or lost attributes in header, footer, navigation and no-results patterns.
- After the fix, all 27 structural, packaging and browser-runner tests passed.
- The real Site Editor parser validated all 19 registered Suppeth patterns, including all 8 header/footer replacement patterns, with retained attributes and valid serialization round trips.
- WordPress 7.1.2 runtime checks, including plain and translated PHP parser round trips, passed on PHP 8.3.33 and 7.4.33.
- WordPress coding standards, PHP/JavaScript syntax checks and deterministic ZIP packaging passed.
- The editor-parser check is now a theme CI gate. Its readiness probe retains Playground's auto-login cookie and waits for the CLI's Ready message before browsing; HTTP alone can respond before blueprint completion. Fresh sessions can explicitly sign in with the public fixture credentials.

Theme pattern-library entries remain read-only by design. Apply a header by editing or replacing its template part, as described in the theme reference.

## Local template showcase

- All 28 structural, packaging and runner tests passed.
- The reusable blueprint created the showcase on WordPress 7.1.2 / PHP 8.3.33; native Page/Post template assignments and route-scoped Index/Archive overrides were verified.
- Re-running the showcase seeder retained one Templates submenu with eight comparison links.
- In the running preview, all eight menu destinations returned HTTP 200 and showed the expected default/sidebar layouts, populated queries and post details.
- All four sidebar examples passed layout checks at 1440px and 390px. The desktop submenu opens on hover; the mobile menu exposes all eight destinations and its Page with Sidebar link navigates correctly.
- Index and Archive pagination advance to distinct second-page posts while retaining their sidebar layouts. The archive pagination regression failed before expanding pattern references and passed after the correction.
- The actual editor parser validated all eight new page/post/menu/template sources without invalid or missing blocks.
- The fixture was applied without resetting the existing preview. Temporary seeding instrumentation was removed; theme runtime files are unchanged.

## Setup scope limits

Browser checks and hosted-site deployment were not run. Theme runtime files and appearance are unchanged.

The coordinated site migration must pin the merged theme commit before using its new artifact workflow. Initial local migration validation used an explicit standalone-checkout override; release validation must use the pin.
