import json
from http.server import BaseHTTPRequestHandler, HTTPServer


class AlertHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b"ready\n")

    def do_POST(self):
        length = int(self.headers.get("Content-Length", "0"))
        body = self.rfile.read(length)
        try:
            payload = json.loads(body) if body else {}
        except json.JSONDecodeError:
            payload = {"raw": body.decode("utf-8", errors="replace")}
        print(json.dumps({"event": "grafana_alert_webhook", "payload": payload}), flush=True)
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b"accepted\n")

    def log_message(self, format, *args):
        return


HTTPServer(("0.0.0.0", 8080), AlertHandler).serve_forever()
