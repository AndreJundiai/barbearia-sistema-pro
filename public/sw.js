/*
 * Service worker do app.
 * - Páginas (HTML): sempre buscam na rede primeiro (preços, horários e token de
 *   segurança precisam estar atualizados). Sem internet, mostra /offline.html.
 * - Arquivos estáticos (ícones, imagens, fontes): cache primeiro.
 * - Nunca intercepta POST, API do Mercado Pago nem o painel.
 */
const VERSION = 'v3';
const STATIC_CACHE = `static-${VERSION}`;
const PRECACHE = ['/offline.html', '/icons/icon-192.png', '/icons/icon-512.png', '/images/hero.jpg'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC_CACHE).then((c) => c.addAll(PRECACHE)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Navegação: rede primeiro, offline como reserva
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
    return;
  }

  // Estáticos do próprio site
  const isStatic = url.origin === self.location.origin && /\.(png|jpe?g|webp|svg|ico|woff2?)$/.test(url.pathname);
  const isFont = url.hostname === 'fonts.gstatic.com';
  if (isStatic || isFont) {
    event.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        if (res.ok) {
          const copy = res.clone();
          caches.open(STATIC_CACHE).then((c) => c.put(req, copy));
        }
        return res;
      }))
    );
  }
});
