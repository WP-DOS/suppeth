"""Regression coverage for Playground's cookie-based readiness redirect.

Package: Suppeth
Since: Suppeth 0.1.6
"""
import importlib.util
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
import tempfile
import threading
from types import SimpleNamespace
import unittest
from unittest.mock import Mock, patch

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location("editor_runner", ROOT / "scripts/check-editor.py")
runner = importlib.util.module_from_spec(spec)
spec.loader.exec_module(runner)


class EditorRunnerTests(unittest.TestCase):
    def test_readiness_retains_auto_login_cookie(self):
        class RedirectHandler(BaseHTTPRequestHandler):
            def do_GET(self):
                if "fixture_ready=1" in self.headers.get("Cookie", ""):
                    self.send_response(200)
                else:
                    self.send_response(302)
                    self.send_header("Set-Cookie", "fixture_ready=1; Path=/")
                    self.send_header("Location", "/")
                self.end_headers()

            def log_message(self, *args):
                pass

        server = ThreadingHTTPServer(("127.0.0.1", 0), RedirectHandler)
        thread = threading.Thread(target=server.serve_forever, kwargs={"poll_interval": 0.01}, daemon=True)
        thread.start()
        try:
            # A broken redirect probe fails immediately rather than waiting 90 seconds.
            for login_required in [False, True]:
                with self.subTest(login_required=login_required):
                    clock = SimpleNamespace(monotonic=Mock(side_effect=[0, 91]), sleep=Mock())
                    with patch.object(runner, "time", clock), patch.object(runner.subprocess, "run") as commands:
                        commands.return_value = SimpleNamespace(stdout="true" if login_required else "false")
                        runner.check("http://127.0.0.1:" + str(server.server_port))
                        self.assertEqual(commands.call_count, 8 if login_required else 5)
                        self.assertEqual(commands.call_args_list[-2].args[0][-2:], ["eval", "--stdin"])
                        self.assertIn("wp.blocks.parse", commands.call_args_list[-2].kwargs["input"])
                        self.assertEqual(commands.call_args_list[-1].args[0][-1], "close")
                        if login_required:
                            self.assertEqual(commands.call_args_list[2].args[0][-3:], ["fill", "#user_login", "admin"])
                            self.assertEqual(commands.call_args_list[3].args[0][-3:], ["fill", "#user_pass", "password"])
                            self.assertEqual(commands.call_args_list[4].args[0][-2:], ["click", "#wp-submit"])
        finally:
            server.shutdown()
            server.server_close()
            thread.join(timeout=2)

    def test_http_ready_is_not_enough_before_blueprint_completion(self):
        with tempfile.TemporaryDirectory() as directory:
            log = Path(directory) / "playground.log"
            log.write_text("Installing WordPress and applying blueprint...")
            clock = SimpleNamespace(monotonic=Mock(side_effect=[0, 91]), sleep=Mock())
            with patch.object(runner, "time", clock), patch.object(runner.subprocess, "run") as commands:
                with patch.object(runner, "build_opener") as opener:
                    with self.assertRaisesRegex(RuntimeError, "did not become ready"):
                        runner.check("http://127.0.0.1:9400", log)
                    opener.return_value.open.assert_not_called()
                    commands.assert_not_called()

    def test_hosted_sites_are_rejected(self):
        with patch.object(runner.subprocess, "run") as commands:
            with self.assertRaises(ValueError):
                runner.check("https://example.com")
            commands.assert_not_called()


if __name__ == "__main__":
    unittest.main()
