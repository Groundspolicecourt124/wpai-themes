// Renders the gallery from gallery.json and wires up live previews + downloads,
// plus tasteful, accessible motion (scroll reveals, pointer tilt, count-up).
document.documentElement.classList.add('js');
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const PLAYGROUND = 'https://playground.wordpress.net/';

// Reveal cards as they scroll into view (staggered). No-op under reduced motion.
const revealObserver = REDUCED ? null : new IntersectionObserver((entries, obs) => {
  for (const e of entries) {
    if (e.isIntersecting) { e.target.classList.add('is-in'); obs.unobserve(e.target); }
  }
}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

const state = { data: null, view: 'themes', q: '', tag: null };

const $ = (s, r = document) => r.querySelector(s);
const grid = $('#grid');
const tpl = $('#card-tpl');

function items() {
  return (state.data?.[state.view] ?? []);
}

function filtered() {
  const q = state.q.trim().toLowerCase();
  return items().filter((it) => {
    if (state.tag && !(it.tags || []).includes(state.tag)) return false;
    if (!q) return true;
    return (
      it.name.toLowerCase().includes(q) ||
      (it.description || '').toLowerCase().includes(q) ||
      (it.tags || []).some((t) => t.includes(q))
    );
  });
}

// Build the Playground live-preview URL from the item's blueprint, resolved
// absolutely against the current site so it works under any Pages path.
function previewUrl(it) {
  const blueprintUrl = new URL(it.blueprint, location.href).href;
  return `${PLAYGROUND}?blueprint-url=${encodeURIComponent(blueprintUrl)}`;
}

function renderChips() {
  const box = $('#chips');
  const all = new Set();
  items().forEach((it) => (it.tags || []).forEach((t) => all.add(t)));
  const tags = [...all].sort();
  box.innerHTML = '';
  if (state.view !== 'themes' || tags.length === 0) return;
  for (const t of tags) {
    const c = document.createElement('button');
    c.className = 'chip' + (state.tag === t ? ' is-active' : '');
    c.textContent = t;
    c.onclick = () => { state.tag = state.tag === t ? null : t; render(); };
    box.appendChild(c);
  }
}

function card(it) {
  const node = tpl.content.firstElementChild.cloneNode(true);
  const pUrl = previewUrl(it);
  const shot = $('.card__shot', node);
  shot.href = pUrl;
  $('img', shot).src = it.screenshot;
  $('img', shot).alt = `${it.name} screenshot`;
  $('.card__name', node).textContent = it.name;
  $('.card__ver', node).textContent = 'v' + it.version;
  $('.card__desc', node).textContent = it.description;
  const tags = $('.card__tags', node);
  (it.tags || []).slice(0, 4).forEach((t) => {
    const s = document.createElement('span');
    s.textContent = t;
    tags.appendChild(s);
  });
  const preview = $('.card__preview', node);
  preview.href = pUrl;
  preview.textContent = it.type === 'plugin' ? 'Try it live' : 'Live preview';
  const dl = $('.card__dl', node);
  dl.href = it.zip;
  dl.setAttribute('download', '');
  return node;
}

function render() {
  document.querySelectorAll('.tab').forEach((t) =>
    t.classList.toggle('is-active', t.dataset.view === state.view));
  renderChips();
  const list = filtered();
  grid.innerHTML = '';
  list.forEach((it, i) => {
    const node = card(it);
    node.classList.add('reveal');
    node.style.setProperty('--i', i % 8);
    grid.appendChild(node);
    if (revealObserver) revealObserver.observe(node);
    else node.classList.add('is-in');
  });
  $('#empty').hidden = list.length > 0;
}

function setStats() {
  const c = state.data?.counts || { themes: 0, plugins: 0 };
  const el = $('#stats');
  el.innerHTML =
    `<span class="stat" data-to="${c.themes}">0</span> theme${c.themes === 1 ? '' : 's'} ` +
    `· <span class="stat" data-to="${c.plugins}">0</span> plugin${c.plugins === 1 ? '' : 's'} · all free · all GPL`;
  el.querySelectorAll('.stat').forEach((s) => {
    const to = +s.dataset.to;
    if (REDUCED || to <= 0) { s.textContent = String(to); return; }
    let cur = 0;
    const step = () => {
      cur += Math.max(1, Math.ceil(to / 16));
      if (cur >= to) { s.textContent = String(to); return; }
      s.textContent = String(cur);
      requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
}

function wire() {
  document.querySelectorAll('.tab').forEach((t) => {
    t.onclick = () => { state.view = t.dataset.view; state.tag = null; render(); };
  });
  $('#search').addEventListener('input', (e) => { state.q = e.target.value; render(); });
}

// Emit an ItemList of every theme/plugin as JSON-LD for richer search results.
function injectItemList(data) {
  const all = [...(data.themes || []), ...(data.plugins || [])];
  if (!all.length) return;
  const ld = {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    name: 'Free WordPress Themes & Plugins',
    numberOfItems: all.length,
    itemListElement: all.map((it, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      item: {
        '@type': 'SoftwareApplication',
        name: it.name,
        description: it.description,
        applicationCategory: it.type === 'plugin' ? 'WordPress Plugin' : 'WordPress Theme',
        operatingSystem: 'WordPress',
        image: new URL(it.screenshot, location.href).href,
        downloadUrl: new URL(it.zip, location.href).href,
        softwareVersion: it.version,
        license: 'https://www.gnu.org/licenses/gpl-2.0.html',
        offers: { '@type': 'Offer', price: '0', priceCurrency: 'USD' },
      },
    })),
  };
  const s = document.createElement('script');
  s.type = 'application/ld+json';
  s.textContent = JSON.stringify(ld);
  document.head.appendChild(s);
}

async function init() {
  wire();
  try {
    const res = await fetch('gallery.json', { cache: 'no-cache' });
    state.data = await res.json();
  } catch {
    state.data = { themes: [], plugins: [], counts: { themes: 0, plugins: 0 } };
  }
  setStats();
  injectItemList(state.data);
  render();
}

init();
