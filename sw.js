// Service Worker otimizado para SUPACO PWA
const CACHE_NAME = "supaco-cache-v2";
const DB_NAME = "supaco-db";
const DB_VERSION = 1;

// Recursos essenciais para funcionamento offline
const STATIC_CACHE_URLS = [
  "/SUAP/",
  "/SUAP/index_pwa.php",
  "/SUAP/base_pwa.php",
  "/SUAP/boletim_offline.php",
  "/SUAP/horarios_offline.php",
  "/SUAP/offline.php",
  "/SUAP/offline.html",
  "/SUAP/manifest.json",
  "/SUAP/assets/js/offline.js",
  "/SUAP/assets/css/dashboard.css",
  "/SUAP/assets/css/simple-style.css",
  "/SUAP/assets/images/logo.png",
  "/SUAP/assets/images/pattern.png",
];

// CDNs que devem ser cacheados
const CDN_CACHE_URLS = [
  "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
  "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js",
  "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css",
];

// Instalação do Service Worker
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then((cache) => {
        console.log("SUPACO PWA: Cache aberto");
        // Cache recursos estáticos primeiro
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        // Cache CDNs de forma opcional (não falha se der erro)
        return caches
          .open(CACHE_NAME)
          .then((cache) =>
            Promise.allSettled(CDN_CACHE_URLS.map((url) => cache.add(url)))
          );
      })
      .then(() => {
        console.log("SUPACO PWA: Recursos cacheados com sucesso");
        self.skipWaiting();
      })
  );
});

// Estratégia de fetch otimizada
self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignora requisições para chrome-extension e outras schemes não suportadas
  if (
    !url.protocol.startsWith("http") ||
    url.protocol === "chrome-extension:"
  ) {
    return;
  }

  // Ignora requisições para outros domínios (exceto API do SUAP)
  if (url.hostname !== location.hostname && !url.hostname.includes("suap.")) {
    return;
  }

  // Diferentes estratégias baseadas no tipo de recurso
  if (
    url.pathname.includes("/api_offline.php") ||
    url.pathname.includes("/api/")
  ) {
    // API: Network First (dados sempre atualizados quando possível)
    event.respondWith(networkFirstStrategy(request));
  } else if (
    url.pathname.endsWith(".css") ||
    url.pathname.endsWith(".js") ||
    url.pathname.endsWith(".png")
  ) {
    // Assets estáticos: Cache First
    event.respondWith(cacheFirstStrategy(request));
  } else if (url.pathname.endsWith(".php") || url.pathname.endsWith(".html")) {
    // Páginas: Stale While Revalidate
    event.respondWith(staleWhileRevalidateStrategy(request));
  } else {
    // Padrão: Cache First com fallback
    event.respondWith(cacheFirstStrategy(request));
  }
});

// Network First - para APIs
async function networkFirstStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok && networkResponse.status < 400) {
      const cache = await caches.open(CACHE_NAME);
      // Só clona se a response não foi usada
      if (!networkResponse.bodyUsed) {
        await cache.put(request, networkResponse.clone());
      }
      return networkResponse;
    }
  } catch (error) {
    console.log("SUPACO PWA: Falha na rede, tentando cache");
  }

  // Fallback para cache
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }

  // Se não há cache e é uma página, retorna offline.html
  if (request.headers.get("accept")?.includes("text/html")) {
    return caches.match("/SUAP/offline.html");
  }

  throw new Error("Recurso não disponível offline");
}

// Cache First - para assets estáticos
async function cacheFirstStrategy(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }

    const networkResponse = await fetch(request);
    if (networkResponse.ok && networkResponse.status < 400) {
      const cache = await caches.open(CACHE_NAME);
      // Só clona se a response não foi usada
      if (!networkResponse.bodyUsed) {
        await cache.put(request, networkResponse.clone());
      }
    }
    return networkResponse;
  } catch (error) {
    console.log("SUPACO PWA: Recurso não disponível:", request.url);
    // Retorna resposta offline se disponível
    return caches.match("/SUAP/offline.html");
  }
}

// Stale While Revalidate - para páginas
async function staleWhileRevalidateStrategy(request) {
  const cachedResponse = await caches.match(request);

  // Busca nova versão em background
  const fetchPromise = fetch(request)
    .then((response) => {
      if (response.ok && response.status < 400 && !response.bodyUsed) {
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(request, response.clone());
        });
      }
      return response;
    })
    .catch(() => null);

  // Retorna cache imediatamente se disponível, senão espera a rede
  return cachedResponse || fetchPromise || caches.match("/SUAP/offline.html");
}

// Ativação otimizada com limpeza de cache
self.addEventListener("activate", (event) => {
  event.waitUntil(
    Promise.all([
      // Limpa caches antigos
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME) {
              console.log("SUPACO PWA: Removendo cache antigo:", cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      // Toma controle imediatamente
      self.clients.claim(),
    ]).then(() => {
      console.log("SUPACO PWA: Service Worker ativado");
    })
  );
});

// Sincronização em background otimizada
self.addEventListener("sync", (event) => {
  if (event.tag === "sync-dados-academicos") {
    event.waitUntil(syncDadosAcademicos());
  }
});

// Função de sincronização otimizada
async function syncDadosAcademicos() {
  try {
    if (!navigator.onLine) {
      console.log("SUPACO PWA: Offline, pulando sincronização");
      return;
    }

    console.log("SUPACO PWA: Iniciando sincronização de dados");

    // Endpoints para sincronizar
    const endpoints = ["boletim", "horarios"];
    const syncPromises = endpoints.map(async (endpoint) => {
      try {
        const response = await fetch(
          `/SUAP/api_offline.php?endpoint=${endpoint}`
        );
        if (response.ok) {
          const data = await response.json();

          // Envia mensagem para a página para atualizar os dados locais
          const clients = await self.clients.matchAll();
          clients.forEach((client) => {
            client.postMessage({
              type: "SYNC_DATA",
              endpoint,
              data,
            });
          });

          console.log(`SUPACO PWA: ${endpoint} sincronizado`);
        }
      } catch (error) {
        console.error(`SUPACO PWA: Erro ao sincronizar ${endpoint}:`, error);
      }
    });

    await Promise.allSettled(syncPromises);

    // Notifica conclusão da sincronização
    const clients = await self.clients.matchAll();
    clients.forEach((client) => {
      client.postMessage({
        type: "SYNC_COMPLETE",
        message: "Dados acadêmicos atualizados",
      });
    });
  } catch (error) {
    console.error("SUPACO PWA: Erro na sincronização:", error);
  }
}

// Listener para mensagens da página
self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  } else if (event.data && event.data.type === "FORCE_SYNC") {
    syncDadosAcademicos();
  }
});
