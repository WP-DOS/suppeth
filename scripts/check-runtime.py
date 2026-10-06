#!/usr/bin/env python3
"""Run real-WordPress checks and require an explicit PHP success receipt."""
import argparse
import json
from pathlib import Path
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]


def check(php="8.3", preview=False):
    with tempfile.TemporaryDirectory() as directory:
        work = Path(directory)
        source = "blueprint.json" if preview else "standards-blueprint.json"
        blueprint = json.loads((ROOT / "tests" / source).read_text())
        blueprint["preferredVersions"]["php"] = php
        if preview:
            shutil.copytree(ROOT / "tests/fixtures", work / "fixtures")
            blueprint["steps"].append({"step": "runPHP", "code": ""})
        blueprint["steps"][-1]["code"] = """<?php
try {
    ob_start();
    CHECKS
    $message = ob_get_clean();
    file_put_contents('/tmp/suppeth-results/result.json', json_encode(array(
        'passed' => true, 'wordpress' => get_bloginfo('version'),
        'php' => PHP_VERSION, 'message' => $message
    )));
} catch (Throwable $error) {
    file_put_contents('/tmp/suppeth-results/result.json', json_encode(array(
        'passed' => false, 'error' => $error->getMessage()
    )));
}
"""
        checks = "require '/tmp/suppeth-tests/standards.php';"
        if preview:
            checks = """require '/wordpress/wp-load.php';
    foreach (array('elements-proof', 'typography-proof', 'journal', 'services', 'portfolio', 'contact') as $slug) {
        if (!get_page_by_path($slug)) { throw new RuntimeException('Missing preview page: ' . $slug); }
    }
    if (get_stylesheet() !== 'suppeth' || wp_count_posts()->publish < 7 || get_comments(array('count' => true)) < 3) {
        throw new RuntimeException('Preview theme, posts or comments are missing.');
    }
    echo 'Fixed preview blueprint seeded successfully.';
"""
        blueprint["steps"][-1]["code"] = blueprint["steps"][-1]["code"].replace("CHECKS", checks)
        path = work / "blueprint.json"
        path.write_text(json.dumps(blueprint))
        subprocess.run([
            "npx", "--yes", "@wp-playground/cli@3.1.57", "run-blueprint",
            "--wp=7.1", "--php=" + php,
            "--mount=" + str(ROOT) + ":/wordpress/wp-content/themes/suppeth",
            "--mount=" + str(ROOT / "tests/fixtures") + ":/tmp/suppeth-tests",
            "--mount=" + str(work) + ":/tmp/suppeth-results",
            "--blueprint=" + str(path), "--blueprint-may-read-adjacent-files",
        ], cwd=ROOT, check=True)
        receipt = work / "result.json"
        if not receipt.is_file():
            raise RuntimeError("Playground exited without a PHP test receipt.")
        result = json.loads(receipt.read_text())
        if result.get("passed") is not True:
            raise RuntimeError(result.get("error", "Runtime checks failed."))
        print(json.dumps(result, indent=2))


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--php", default="8.3", choices=["7.4", "8.3"])
    parser.add_argument("--preview", action="store_true", help="Verify the fixed synthetic preview blueprint")
    args = parser.parse_args()
    check(args.php, args.preview)
