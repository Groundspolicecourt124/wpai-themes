// Zip every theme and plugin into docs/downloads/<slug>.zip.
// The archive's top-level folder is the slug, so unzipping yields `slug/...`
// exactly as WordPress expects on upload.
import { createWriteStream } from 'node:fs';
import { join } from 'node:path';
import archiver from 'archiver';
import { listThemes, listPlugins, DOCS_DIR, ensureDir } from './lib.mjs';

function zipDir(srcDir, topName, outPath) {
  return new Promise((resolve, reject) => {
    const output = createWriteStream(outPath);
    const archive = archiver('zip', { zlib: { level: 9 } });
    output.on('close', () => resolve(archive.pointer()));
    archive.on('warning', (err) => { if (err.code !== 'ENOENT') reject(err); });
    archive.on('error', reject);
    archive.pipe(output);
    archive.directory(srcDir, topName);
    archive.finalize();
  });
}

const outDir = join(DOCS_DIR, 'downloads');
ensureDir(outDir);

const items = [
  ...listThemes().map((t) => ({ ...t, kind: 'theme' })),
  ...listPlugins().map((p) => ({ ...p, kind: 'plugin' })),
];

let count = 0;
for (const it of items) {
  const bytes = await zipDir(it.dir, it.slug, join(outDir, `${it.slug}.zip`));
  console.log(`  zip   ${it.kind.padEnd(6)} ${it.slug}.zip  (${(bytes / 1024).toFixed(0)} KB)`);
  count++;
}
console.log(`✓ ${count} zip(s) → docs/downloads/`);
