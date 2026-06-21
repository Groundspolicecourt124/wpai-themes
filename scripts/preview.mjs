// Local preview: build the gallery with localhost URLs baked into the Playground
// blueprints (so "Live preview" works against this machine), then serve it.
// Cross-platform — sets SITE_URL itself instead of relying on shell env syntax.
import { spawnSync, spawn } from 'node:child_process';

const PORT = process.env.PORT || 4173;
const env = { ...process.env, PORT: String(PORT), SITE_URL: `http://localhost:${PORT}/` };

for (const step of ['gen-screenshots', 'build-zips', 'gen-gallery']) {
  const r = spawnSync(process.execPath, [`scripts/${step}.mjs`], { stdio: 'inherit', env });
  if (r.status !== 0) process.exit(r.status ?? 1);
}

console.log('');
spawn(process.execPath, ['scripts/serve-gallery.mjs'], { stdio: 'inherit', env });
