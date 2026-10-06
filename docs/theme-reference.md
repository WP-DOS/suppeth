# Theme behavior reference

Current behavior, not the future neutral-design specification. Run browser checks from the theme repository; see [development](development.md).

## Customize

Template-owned text comes from hidden PHP patterns so headings, labels, navigation, and recovery messages can be translated. Bundled translations can live under `languages/`. These remain native editable blocks; saved Site Editor customizations are not automatically replaced or translated by a theme update.

The default homepage is the latest-posts index. An empty site shows a no-results message and search. For a static homepage, create a page and select it under **Settings → Reading**. Navigation uses the native block; add links in the Site Editor.

> [!IMPORTANT]
> Site Editor template and Global Styles changes are stored in the database and override theme files. Export intentional reusable theme changes into the Suppeth repository; retain site-specific overrides in the site's database. If a template update appears to have no effect, inspect its saved customization before resetting anything.


## Sidebar templates

Four optional templates are available in **Appearance → Editor → Design → Templates**: **Page with Sidebar**, **Post with Sidebar**, **Index with Sidebar**, and **Archive with Sidebar**. Each uses a wide two-column layout that stacks content above the sidebar on mobile. The existing default templates stay unchanged.

Choose **Page with Sidebar** or **Post with Sidebar** from the template setting when editing a page or post. Index and archive variants are Site Editor starting points, not automatic replacements: copy their Columns block into the active Index/Home or Archive template to use that layout. They retain the inherited query, pagination, and no-results state.

Edit the shared **Sidebar** template part in the Site Editor to change its categories and recent posts or replace them with other blocks. Changes apply to all four variants. Wide and full-width page/post blocks stay inside the content column in sidebar layouts; use the default template for edge-to-edge sections. Run `tests/sidebar-layout.browser.js` on each variant at desktop and mobile widths to check stacking, landmarks, and content bounds.


## Header patterns

Four optional patterns are available under **Headers** in the Site Editor: **Header with search**, **Centered header**, **Two-row header with search**, and **Sticky header**. Edit the Header template part, then use its replacement patterns or replace its blocks with a pattern from the inserter. The default header stays unchanged until you choose a replacement.

Each pattern uses native Site Logo, Site Title, and Navigation blocks. Set a logo in the Site Logo block and choose your menu in Navigation; patterns contain no fixed links or menu IDs. The search variants use the native Search block. All blocks remain editable. Theme version 0.1.5 invalidates WordPress's cached pattern list and the theme stylesheet on deployment.

The sticky variant keeps the entire Header template part visible while scrolling, with a solid background and an offset for the WordPress admin bar. It is intended for the Header template part, not insertion inside page content. Keep this header compact so it leaves room for reading on small screens. The Site Editor canvas is for editing; check sticky behavior on the frontend.


## Footer patterns

Four optional patterns are available under **Footers** in the Site Editor: **Compact footer**, **Centered footer**, **Footer with columns**, and **Footer with search**. Edit the Footer template part, then choose a replacement pattern or replace its blocks using the inserter. The default footer stays unchanged until you choose one; saved Site Editor customizations still take precedence over theme files.

Each uses native Site Title and Navigation blocks, with no fixed links or menu IDs. Choose your footer menu in Navigation; its links stay visible and wrap on small screens rather than opening an overlay. The centered variant includes an optional Site Logo and the site description. The columns variant adds the site description and three recent posts, stacking vertically on mobile. The search variant includes the site description and a labelled native Search block. All blocks, headings, and the WordPress credit remain editable.

Check each replacement on desktop and mobile with `tests/footer-patterns.browser.js`, including long menu labels and sites without a logo or posts.


## Details blocks

Native Details blocks show a plus that rotates 45° into an X. Consecutive sibling Details blocks share borders and outer corners, forming one visual group; each still opens independently. The frontend conditionally loads a small height-animation script, preserving keyboard interaction and moving focus back to the summary when closing focused content. Opening and closing take 280 ms; reduced-motion preferences disable both the slide and icon transition. Without JavaScript, native disclosure behavior and the icon still work. Saved block markup is not changed, and ALT disclosures are unaffected. Editor styles share the icon and grouping; the slide enhancement is frontend-only.

Check `/services/` with `tests/details.browser.js` under normal and reduced motion, including rapid toggling and keyboard activation.


## Wide content and image descriptions

Normal blocks use the 720 px content width; wide blocks can reach 1120 px; full-width blocks reach the viewport edges. Text inside a full-width section can remain constrained. Page and post content wrappers allow these alignments without viewport-width CSS hacks.

On the frontend, Image and Featured Image blocks with non-empty alt text can get a white ALT badge at bottom-left. A conditional, dependency-free script reveals it only when the displayed image is at least 320 × 160 px. Thumbnail blocks, floated images, Media & Text images, and cover backgrounds are excluded. Without JavaScript, the extra control stays hidden and the original alt remains available to assistive technology.

The panel grows upward/right while keeping an 8 px inset and a maximum width of 26 rem. Click, Enter, and Space toggle it; Escape closes it. The 200 ms size animation is disabled for reduced motion. Long descriptions scroll inside the image bounds. Gallery captions wrap below the images on a plain background, never around the control. Visible descriptions supplement meaningful alt text; they do not replace it.

This enhancement is added during server rendering, so the editor keeps the original image markup. Third-party `picture` markup is left unchanged. Test linked images, galleries, short/long descriptions, keyboard focus, and small screens before release.

```sh
npx agent-browser --session suppeth-styles eval --stdin < tests/image-layout.browser.js
```


## Online test checklist

Keep the test site private. After activation, check the empty homepage, then create a test post with a long title, headings, lists, links, an image, and a code block. Check a page, category archive, search with and without results, and an unknown URL. Inspect desktop and mobile layouts, keyboard focus, the navigation overlay, comments, and pagination. Confirm that the Site Editor matches the frontend.

Measure performance with representative content and plugins. The theme serves its three Latin-subset variable fonts locally (about 90 KB combined); it requests no remote fonts. Font licenses and source details live in `assets/fonts/`. Other scripts use the system fallback for glyphs outside the bundled subsets. Its image-description script is loaded only when described image blocks are rendered; core blocks and plugins can still add assets. Structural checks do not replace testing in WordPress.

Local credentials and Pi session files stay out of Git and deployment.
