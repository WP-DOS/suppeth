# Suppeth purpose and boundaries

## Purpose

Suppeth is a free GPL-licensed WordPress block theme: an almost blank slate that people can adapt to their own design through the Site Editor. Ordinary site building should not require custom CSS, PHP, third-party blocks, or plugins; code remains an optional escape hatch.

Plain but usable means complete core templates, accessible defaults, restrained typography and spacing, and minimal decoration. Full FSE support means native templates, template parts, Global Styles, and normal Site Editor customization, not compatibility with every plugin or historical WordPress release.

## Current state and direction

Suppeth uses neutral native defaults and editable templates, with optional color/font variations and header, footer, and sidebar patterns. Ordinary customization must work through native block controls and Global Styles. Unsupported custom behavior and block-scoped CSS strings are outside the theme's scope: placing CSS in theme.json does not make it a native FSE control. A small, documented stylesheet may supply non-decorative usability guards where core needs help, such as wrapping long text, scrolling preformatted lines, anchor offsets, and template-part spacing. These guards must not override editable colors, typography, borders, or other design choices.

Image Descriptions is a separately owned WordPress plugin. Suppeth neither imports nor installs it. Site adoption and any plugin submodule belong to the site repository. Details blocks retain WordPress's native presentation and behavior.

The development fixtures contain synthetic content for exercising the theme. The minimal three-post showcase is a test harness, not demo content shipped to users or a copy of wp-dos.com.

## Ownership

This repository owns theme code, tests, fixtures, coding standards, packaging and CI. [WP-DOS/site](https://github.com/WP-DOS/site) imports a released commit as a submodule and owns website integration and deployment. Theme contributors need no site checkout.

wp-dos.com is an education-first WordPress publication. Suppeth may power it without becoming a dependency for its educational examples.

## Deferred decisions

- Storage, publication and export of article sources and public portable patterns.
- Broader compatibility matrix beyond the declared metadata and verified checks.

See the site's [project boundaries](https://github.com/WP-DOS/site/blob/develop/docs/project.md) for site-owned integration and deployment decisions.
