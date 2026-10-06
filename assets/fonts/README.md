# Bundled fonts

DM Sans (body), Manrope (headings), and JetBrains Mono (code) are locally served Latin-subset variable WOFF2 files, registered using `fontFace` and `file:./` paths in `theme.json`. No font stylesheet or font binary is requested from a third-party host at runtime. System sans-serif fallbacks supply glyphs outside these subsets. Normal weights 400–600 are registered for DM Sans/Manrope and 400–700 for JetBrains Mono; italic text uses browser synthesis.

Downloaded from Google Fonts on October 5, 2026:

- DM Sans: https://fonts.gstatic.com/s/dmsans/v17/rP2Yp2ywxg089UriI5-g4vlH9VoD8Cmcqbu0-K6z9mXg.woff2
- Manrope: https://fonts.gstatic.com/s/manrope/v20/xn7gYHE41ni1AdIRggexSvfedN4.woff2

JetBrains Mono was added on October 6, 2026:

- Font: https://fonts.gstatic.com/s/jetbrainsmono/v24/tDbv2o-flEEny0FZhsfKu5WU4zr3E_BX0PnT8RD8yKwBNntkaToggR7BYRbKPxDcwgknk-4.woff2
- License: `JetBrains-Mono-OFL.txt`, from `ofl/jetbrainsmono/OFL.txt` in the same Google Fonts repository.

All three are distributed under the SIL Open Font License 1.1. Copyright notices and full license terms are included in `DM-Sans-OFL.txt` and `Manrope-OFL.txt`, sourced from the respective `ofl/dmsans` and `ofl/manrope` directories in https://github.com/google/fonts.
