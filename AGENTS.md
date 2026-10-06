# Suppeth contributor guidance

Read [CONTEXT.md](CONTEXT.md) for terminology and [docs/project.md](docs/project.md) before changing product behavior.

- Implement and validate all theme work here, including fixtures, packaging, and CI. The site consumes a pinned submodule.
- Before development or validation, read [docs/development.md](docs/development.md). Before template or styling changes, read [docs/theme-reference.md](docs/theme-reference.md).
- Use native editable blocks and Site Editor settings for ordinary customization. Keep accessible, usable defaults; the neutral-design goal is not permission to remove behavior without a scoped change.
- Keep synthetic fixtures in tests; distribute only runtime files through the packaging allowlist.
- Feature PRs target main. Site adoption is a separate PR into its develop branch after the theme change merges.
- For site integration, read the site's [AGENTS.md](https://github.com/WP-DOS/site/blob/develop/AGENTS.md) and [development guide](https://github.com/WP-DOS/site/blob/develop/docs/development.md). Local sibling checkout: ../site/. Within a site submodule: ../../.
- Keep credentials, real site content, and local agent state out of Git. Live deployment and database operations require explicit approval.
