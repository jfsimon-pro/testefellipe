const CACHE_NAME = 'pwa-educacional-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/meus-cursos.php',
  '/cursos-matriculados.php',
  '/perfil.php',
  // Adicione outros arquivos estáticos importantes, como CSS e JS
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(key => key !== CACHE_NAME)
            .map(key => caches.delete(key))
      )
    )
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
}); 