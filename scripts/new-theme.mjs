// Scaffold a new theme by cloning the reference theme (aurora) and swapping the
// name/slug/text-domain tokens. Usage: npm run new:theme "My Theme Name"
import { cpSync, existsSync, readdirSync, readFileSync, writeFileSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { THEMES_DIR } from './lib.mjs';

const REF_SLUG = 'aurora';
const REF_NAME = 'Aurora';

const rawName = process.argv.slice(2).join(' ').trim();
if (!rawName) {
  console.error('Usage: npm run new:theme "My Theme Name"');
  process.exit(1);
}
const slug = rawName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const dest = join(THEMES_DIR, slug);
const ref = join(THEMES_DIR, REF_SLUG);

if (!existsSync(ref)) { console.error(`Reference theme "${REF_SLUG}" not found.`); process.exit(1); }
if (existsSync(dest)) { console.error(`themes/${slug} already exists.`); process.exit(1); }

cpSync(ref, dest, { recursive: true });

function walk(dir) {
  for (const e of readdirSync(dir)) {
    const f = join(dir, e);
    if (statSync(f).isDirectory()) { walk(f); continue; }
    if (/\.(png|jpg|jpeg|gif)$/i.test(f)) continue;
    const before = readFileSync(f, 'utf8');
    const after = before
      .split(REF_NAME).join(rawName)
      .split(REF_SLUG).join(slug);
    if (after !== before) writeFileSync(f, after);
  }
}
walk(dest);

console.log(`✓ Created themes/${slug}  (from ${REF_SLUG})`);
console.log(`  Next: edit themes/${slug}/style.css, then  npm run dev ${slug}`);
