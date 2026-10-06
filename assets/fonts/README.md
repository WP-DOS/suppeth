# Bundled fonts

The theme defaults to system sans-serif. The optional **Serif** typeset pairs **Source Serif 4** with **Cormorant Garamond** for headings and the site title; **Sans** pairs **DM Sans** with **Manrope**. **JetBrains Mono** is the code preset. Existing saved font selections remain editable.

All faces are locally served Latin-subset variable WOFF2 files registered through `fontFace` and `file:./` paths in `theme.json`. No font stylesheet or binary is requested from a third-party host at runtime. Georgia/Times fallbacks supply serif glyphs outside these subsets; the sans and mono presets keep their own fallbacks.

Source Serif 4 includes normal and true italic faces with optical sizing, registered at weights 400–700. Cormorant Garamond provides normal weights 400–700; italic uses synthesis. DM Sans/Manrope retain normal weights 400–600, and JetBrains Mono 400–700. The Serif typeset's normal body and heading faces total about 156 KB. Bundled faces load when used rather than supplying the default system typography.

## Sources and licenses

Downloaded from Google Fonts on October 6, 2026:

- Cormorant Garamond (normal): https://fonts.gstatic.com/s/cormorantgaramond/v21/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYqXtKky2F7g.woff2
- Source Serif 4 (italic): https://fonts.gstatic.com/s/sourceserif4/v15/vEFK2_tTDB4M7-auWDN0ahZJW1gewtW_WpzEpMs.woff2
- Source Serif 4 (normal): https://fonts.gstatic.com/s/sourceserif4/v15/vEFI2_tTDB4M7-auWDN0ahZJW1gb8te1Xb7G.woff2

Earlier bundled faces:

- DM Sans: https://fonts.gstatic.com/s/dmsans/v17/rP2Yp2ywxg089UriI5-g4vlH9VoD8Cmcqbu0-K6z9mXg.woff2 (October 5, 2026)
- Manrope: https://fonts.gstatic.com/s/manrope/v20/xn7gYHE41ni1AdIRggexSvfedN4.woff2 (October 5, 2026)
- JetBrains Mono: https://fonts.gstatic.com/s/jetbrainsmono/v24/tDbv2o-flEEny0FZhsfKu5WU4zr3E_BX0PnT8RD8yKwBNntkaToggR7BYRbKPxDcwgknk-4.woff2 (October 6, 2026)

All families are distributed under the SIL Open Font License 1.1. Their copyright notices and full terms are bundled in `Source-Serif-4-OFL.txt`, `Cormorant-Garamond-OFL.txt`, `DM-Sans-OFL.txt`, `Manrope-OFL.txt`, and `JetBrains-Mono-OFL.txt`. License sources are the corresponding `ofl/sourceserif4`, `ofl/cormorantgaramond`, `ofl/dmsans`, `ofl/manrope`, and `ofl/jetbrainsmono` directories in https://github.com/google/fonts.
