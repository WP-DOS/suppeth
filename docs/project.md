# Suppeth purpose and boundaries

## Purpose

Suppeth is a free GPL-licensed WordPress block theme: an almost blank slate that people can adapt to their own design through the Site Editor. Ordinary site building should not require custom CSS, PHP, third-party blocks, or plugins; code remains an optional escape hatch.

Plain but usable means complete core templates, accessible defaults, restrained typography and spacing, and minimal decoration. Full FSE support means native templates, template parts, Global Styles, and normal Site Editor customization, not compatibility with every plugin or historical WordPress release.

## Current state and direction

The current theme has an editorial design, local fonts, optional headers, footers and sidebars, plus image-description and Details enhancements. This setup preserves that behavior. Neutralizing those defaults is future, separately scoped work.

The development fixtures contain synthetic content for exercising the theme. Its fictional editorial site is a test harness, not demo content shipped to users or a copy of wp-dos.com.

## Ownership

This repository owns theme code, tests, fixtures, coding standards, packaging and CI. [WP-DOS/site](https://github.com/WP-DOS/site) imports a released commit as a submodule and owns website integration and deployment. Theme contributors need no site checkout.

wp-dos.com is an education-first WordPress publication. Suppeth may power it without becoming a dependency for its educational examples.

## Deferred decisions

- Storage, publication and export of article sources and public portable patterns.
- Neutral-design implementation and individual defaults.
- Broader compatibility matrix beyond the declared metadata and verified checks.

See the site's [project boundaries](https://github.com/WP-DOS/site/blob/develop/docs/project.md) for future plugin ownership.
