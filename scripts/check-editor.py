#!/usr/bin/env python3
"""Validate registered patterns through the running WordPress Site Editor."""
import argparse
from http.cookiejar import CookieJar
from pathlib import Path
import subprocess
import time
from urllib.error import URLError
from urllib.parse import urlparse
from urllib.request import build_opener, HTTPCookieProcessor

ROOT = Path(__file__).resolve().parents[1]


def check(url):
    parsed = urlparse(url)
    if parsed.scheme != "http" or parsed.hostname not in {"127.0.0.1", "localhost"}:
        raise ValueError("Editor regression checks require a disposable local sandbox.")
    # Playground's automatic login redirects until its cookies are retained.
    opener = build_opener(HTTPCookieProcessor(CookieJar()))
    deadline = time.monotonic() + 90
    while True:
        try:
            with opener.open(url, timeout=2) as response:
                if response.status == 200:
                    break
        except (URLError, TimeoutError):
            pass
        if time.monotonic() >= deadline:
            raise RuntimeError("Local Playground did not become ready within 90 seconds.")
        time.sleep(0.5)
    command = ["npx", "--yes", "agent-browser@0.38.2", "--session", "suppeth-pattern-validation"]
    try:
        subprocess.run(command + ["open", url.rstrip("/") + "/wp-admin/site-editor.php"], check=True)
        login = subprocess.run(command + ["eval", "Boolean(document.querySelector('#loginform'))"],
                               capture_output=True, text=True, check=True)
        if login.stdout.strip() == "true":
            # Public blueprint credentials only; this runner rejects hosted sites.
            subprocess.run(command + ["fill", "#user_login", "admin"], check=True)
            subprocess.run(command + ["fill", "#user_pass", "password"], check=True)
            subprocess.run(command + ["click", "#wp-submit"], check=True)
        subprocess.run(command + ["wait", "--fn", "Boolean(window.wp?.blocks && window.wp?.apiFetch)"], check=True)
        subprocess.run(command + ["eval", "--stdin"],
                       input=(ROOT / "tests/pattern-validation.browser.js").read_text(),
                       text=True, check=True)
    finally:
        subprocess.run(command + ["close"], check=False)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--url", default="http://127.0.0.1:9400")
    check(parser.parse_args().url)
