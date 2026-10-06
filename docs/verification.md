# Setup verification

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

## Scope limits

Browser checks and hosted-site deployment were not run. Theme runtime files and appearance are unchanged.

The coordinated site migration must pin the merged theme commit before using its new artifact workflow. Initial local migration validation used an explicit standalone-checkout override; release validation must use the pin.
