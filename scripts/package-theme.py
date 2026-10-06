#!/usr/bin/env python3
"""Create a deterministic WordPress theme ZIP using only the standard library.

Package: Suppeth
Since: Suppeth 0.1.6
"""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT
RUNTIME_DIRECTORIES = ("assets", "parts", "patterns", "templates", "languages", "styles")
RUNTIME_FILES = ("style.css", "theme.json", "functions.php", "readme.txt", "LICENSE", "screenshot.png")


def runtime_files():
    """Allow only distributable theme files, never contributor tooling or fixtures."""
    paths = [THEME / name for name in RUNTIME_FILES]
    for name in RUNTIME_DIRECTORIES:
        paths.extend((THEME / name).rglob("*"))
    return sorted(path for path in paths if path.is_file()
                  and not any(part.startswith(".") for part in path.relative_to(THEME).parts))

def package(output=None):
    output = Path(output) if output else ROOT / "dist" / "suppeth.zip"
    output.parent.mkdir(parents=True, exist_ok=True)
    with ZipFile(output, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
        for path in runtime_files():
            if path.is_file():
                info = ZipInfo("suppeth/" + path.relative_to(THEME).as_posix())
                info.date_time = (2026, 1, 1, 0, 0, 0)
                info.external_attr = 0o100644 << 16
                info.compress_type = ZIP_DEFLATED
                archive.writestr(info, path.read_bytes())
    return output

if __name__ == "__main__":
    print(package())
