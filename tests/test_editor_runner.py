"""Regression coverage for Playground's cookie-based readiness redirect."""
import importlib.util
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
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
            clock = SimpleNamespace(monotonic=Mock(side_effect=[0, 91]), sleep=Mock())
            with patch.object(runner, "time", clock), patch.object(runner.subprocess, "run") as commands:
                runner.check("http://127.0.0.1:" + str(server.server_port))
                self.assertEqual(commands.call_count, 4)
                self.assertEqual(commands.call_args_list[2].args[0][-2:], ["eval", "--stdin"])
                self.assertIn("wp.blocks.parse", commands.call_args_list[2].kwargs["input"])
                self.assertEqual(commands.call_args_list[-1].args[0][-1], "close")
        finally:
            server.shutdown()
            server.server_close()
            thread.join(timeout=2)

    def test_hosted_sites_are_rejected(self):
        with patch.object(runner.subprocess, "run") as commands:
            with self.assertRaises(ValueError):
                runner.check("https://example.com")
            commands.assert_not_called()


if __name__ == "__main__":
    unittest.main()
