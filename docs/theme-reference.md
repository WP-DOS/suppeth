# Theme behavior reference

Suppeth provides a neutral, editable starting point. Appearance is configured through native Global Styles and block controls. A small stylesheet, shared with the editor, supplies usability guards rather than a prescribed design: long-content wrapping, horizontal scrolling for preformatted text, in-page anchor offsets, and template-part margin cleanup. It adds no forced decoration, animations, or frontend JavaScript.

## Defaults and variations

The default uses system sans-serif typography and a white/gray palette with blue links. Body text is 18 px; headings have an explicit, editable descending scale. Layout widths, root padding, spacing, fonts, and colors remain native Site Editor settings.

Under **Styles → Typography**, choose **Serif** (Source Serif 4/Cormorant Garamond) or **Sans** (DM Sans/Manrope). Under **Styles → Colors → Edit palette**, choose **Paper** or **Neutral** independently. Bundled font files stay local and are requested when used. See [font sources and licenses](../assets/fonts/README.md).

Variations offer choices rather than imposing an editorial design. The theme does not add custom CSS to nested block markup, animated page effects, image disclosures, or enhanced Details behavior.

## Templates and patterns

Each templates/ file references a complete hidden PHP pattern. These are native editable template blocks; labels and recovery messages are translated. Saved Site Editor changes override theme files.

The default homepage is the latest-posts index. Set a static homepage under **Settings → Reading** if desired. Default page, post, index, archive, search, and 404 templates provide usable navigation and content structure. Pagination starts with native previous/next blocks; users can add pagination numbers in the Site Editor.

Page with Sidebar and Post with Sidebar are assignable templates. Index with Sidebar and Archive with Sidebar are Site Editor starting points. All share the editable Sidebar part and use native Columns that stack on small screens.

Three optional header parts/patterns provide search, centered, and two-row layouts. Four footer alternatives provide compact, centered, columns, and search layouts. Select and replace a template part in the Site Editor; bundled pattern definitions themselves are read-only. Links, menus, search widths, and native block settings remain editable. No sticky-header workaround is imposed.

Author details use separate native Avatar, Author Name, and Author Biography blocks, so avatar shape is editable rather than supplied through a nested-image CSS override.

## Native blocks

Details uses WordPress's native disclosure and keyboard behavior, without theme-injected wrappers, icons, or animation.

Images, featured images, gallery captions, tables, comments, and controls keep native markup. Supported settings can be changed through Global Styles and per-block controls. The theme does not rewrite image HTML or add scripts to it. Long words and displayed URLs wrap within their available width; preformatted lines can scroll locally without widening the page. These baseline guards are explicit in style.css, not hidden in block-scoped theme.json CSS.

Image Descriptions is a standalone plugin maintained in a separate repository. Suppeth has no import, activation step, fixture dependency, or submodule for it. The site may install it independently.

## Local checks

See [development](development.md) for the disposable preview and test commands. Check desktop and mobile layouts, keyboard navigation, search with/without results, an unknown URL, comments, native Details, and gallery captions. Confirm that editor and frontend agree.

The local contact form is a test helper with its own stylesheet; it does not send or store messages and is not packaged with the theme. Credentials, fixtures, documentation, and development dependencies remain outside the runtime ZIP.
