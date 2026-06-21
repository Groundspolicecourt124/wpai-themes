// Tiny static server to preview the built gallery locally. Usage: npm run serve
import { createServer } from 'node:http';
import { readFile } from 'node:fs/promises';
import { join, extname, normalize, sep } from 'node:path';
import { DOCS_DIR } from './lib.mjs';

const PORT = process.env.PORT || 4173;
const MIME = {
  '.html': 'text/html', '.css': 'text/css', '.js': 'text/javascript',
  '.json': 'application/json', '.png': 'image/png', '.svg': 'image/svg+xml',
  '.zip': 'application/zip', '.txt': 'text/plain', '.ico': 'image/x-icon',
};

createServer(async (req, res) => {
  try {
    let p = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
    if (p.endsWith('/')) p += 'index.html';
    const file = normalize(join(DOCS_DIR, p));
    if (!file.startsWith(DOCS_DIR + sep) && file !== DOCS_DIR) {
      res.writeHead(403).end('forbidden');
      return;
    }
    const body = await readFile(file);
    res.writeHead(200, {
      'content-type': MIME[extname(file)] || 'application/octet-stream',
      // Let WordPress Playground fetch blueprints + zips from this local server.
      'access-control-allow-origin': '*',
    });
    res.end(body);
  } catch {
    res.writeHead(404, {
      'content-type': 'text/plain',
      'access-control-allow-origin': '*',
    }).end('404 Not Found');
  }
}).listen(PORT, () => console.log(`Gallery → http://localhost:${PORT}  (build first with: npm run build)`));
