// SUPACO PWA - Gerenciador de Cache Avançado e Visual Offline
const OfflineCacheManager = (() => {
  // Configurações
  const CONFIG = {
    DB_NAME: "supaco-cache-db",
    DB_VERSION: 2,
    STORES: {
      ACADEMIC_DATA: "academic-data",
      USER_DATA: "user-data",
      APP_CONFIG: "app-config",
      SYNC_QUEUE: "sync-queue",
    },
    CACHE_DURATION: {
      BOLETIM: 7 * 24 * 60 * 60 * 1000, // 7 dias
      HORARIOS: 30 * 24 * 60 * 60 * 1000, // 30 dias
      PERFIL: 24 * 60 * 60 * 1000, // 1 dia
    },
    SYNC_INTERVAL: 2 * 60 * 1000, // 2 minutos
    VISUAL_UPDATE_INTERVAL: 1000, // 1 segundo
  };

  let db = null;
  let isOnline = navigator.onLine;
  let syncTimer = null;
  let visualTimer = null;
  let currentVisualState = null;

  // ===========================
  // INICIALIZAÇÃO
  // ===========================

  async function init() {
    try {
      await initDatabase();
      setupEventListeners();
      startVisualManager();
      startSyncManager();

      console.log("✅ OfflineCacheManager inicializado");
      return true;
    } catch (error) {
      console.error("❌ Erro ao inicializar OfflineCacheManager:", error);
      return false;
    }
  }

  // Inicializa o banco de dados
  async function initDatabase() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(CONFIG.DB_NAME, CONFIG.DB_VERSION);

      request.onerror = () => reject(request.error);
      request.onsuccess = () => {
        db = request.result;
        resolve(db);
      };

      request.onupgradeneeded = (event) => {
        const database = event.target.result;

        // Criar stores se não existirem
        Object.values(CONFIG.STORES).forEach((storeName) => {
          if (!database.objectStoreNames.contains(storeName)) {
            const store = database.createObjectStore(storeName, {
              keyPath: "id",
            });
            store.createIndex("timestamp", "timestamp", { unique: false });
            store.createIndex("type", "type", { unique: false });
          }
        });
      };
    });
  }

  // Configura listeners de eventos
  function setupEventListeners() {
    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);
    window.addEventListener("focus", handleWindowFocus);
    window.addEventListener("beforeunload", handleBeforeUnload);

    // Service Worker messages
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.addEventListener(
        "message",
        handleServiceWorkerMessage
      );
    }
  }

  // ===========================
  // GERENCIADOR VISUAL
  // ===========================

  function startVisualManager() {
    updateVisualState();
    visualTimer = setInterval(updateVisualState, CONFIG.VISUAL_UPDATE_INTERVAL);
  }

  function updateVisualState() {
    const newState = {
      isOnline: navigator.onLine,
      timestamp: Date.now(),
      hasOfflineData: false,
      lastSync: null,
    };

    // Verificar se há dados offline
    checkOfflineDataAvailability().then((hasData) => {
      newState.hasOfflineData = hasData;

      // Verificar último sync
      getLastSyncTime().then((lastSync) => {
        newState.lastSync = lastSync;

        // Atualizar visual apenas se houve mudança
        if (JSON.stringify(newState) !== JSON.stringify(currentVisualState)) {
          currentVisualState = newState;
          updateVisualElements(newState);
        }
      });
    });
  }

  function updateVisualElements(state) {
    updateConnectionIndicator(state);
    updateOfflineBanner(state);
    updatePageElements(state);
    updateSyncStatus(state);
  }

  // Atualiza indicador de conexão
  function updateConnectionIndicator(state) {
    let indicator = document.getElementById("connection-indicator");

    if (!indicator) {
      indicator = createConnectionIndicator();
    }

    const { isOnline, hasOfflineData, lastSync } = state;

    indicator.className = `offline-indicator ${
      isOnline ? "online" : "offline"
    }`;

    if (isOnline) {
      indicator.innerHTML = `
        <i class="fas fa-wifi"></i>
        <span>Online</span>
      `;
    } else {
      const dataAge = lastSync ? getDataAge(lastSync) : null;
      indicator.innerHTML = `
        <i class="fas fa-wifi-slash"></i>
        <span>Offline</span>
        ${
          hasOfflineData
            ? `<small>• Dados: ${dataAge || "Disponíveis"}</small>`
            : ""
        }
      `;
    }
  }

  // Cria indicador de conexão
  function createConnectionIndicator() {
    const indicator = document.createElement("div");
    indicator.id = "connection-indicator";
    indicator.className = "offline-indicator";
    document.body.appendChild(indicator);
    return indicator;
  }

  // Atualiza banner offline
  function updateOfflineBanner(state) {
    let banner = document.getElementById("offline-banner");

    if (!state.isOnline && !banner) {
      banner = createOfflineBanner();
    }

    if (banner) {
      if (state.isOnline) {
        banner.classList.remove("show");
      } else {
        banner.classList.add("show");
        updateBannerContent(banner, state);
      }
    }
  }

  // Cria banner offline
  function createOfflineBanner() {
    const banner = document.createElement("div");
    banner.id = "offline-banner";
    banner.className = "offline-banner";
    document.body.appendChild(banner);
    return banner;
  }

  // Atualiza conteúdo do banner
  function updateBannerContent(banner, state) {
    const { hasOfflineData, lastSync } = state;
    const dataAge = lastSync ? getDataAge(lastSync) : null;

    banner.innerHTML = `
      <div class="container">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Você está offline</span>
        ${
          hasOfflineData
            ? `<span>• Dados salvos: ${dataAge || "Disponíveis"}</span>`
            : `<span>• Alguns recursos podem não estar disponíveis</span>`
        }
      </div>
    `;
  }

  // Atualiza elementos da página
  function updatePageElements(state) {
    // Adicionar classe offline aos cards
    const cards = document.querySelectorAll(".card");
    cards.forEach((card) => {
      if (state.isOnline) {
        card.classList.remove("offline-mode");
      } else {
        card.classList.add("offline-mode");

        // Adicionar badge offline se não existir
        if (!card.querySelector(".offline-badge")) {
          const badge = document.createElement("div");
          badge.className = "offline-badge";
          badge.innerHTML = '<i class="fas fa-cloud-download-alt"></i> Offline';
          card.style.position = "relative";
          card.appendChild(badge);
        }
      }
    });

    // Atualizar indicadores de status existentes
    const statusIndicators = document.querySelectorAll(
      '[id*="offlineStatusIndicator"], [id*="StatusIndicator"]'
    );
    statusIndicators.forEach((indicator) => {
      if (state.isOnline) {
        indicator.innerHTML = `<span class="badge bg-success"><i class="fas fa-wifi me-1"></i> Online</span>`;
      } else {
        indicator.innerHTML = `<span class="badge bg-warning"><i class="fas fa-wifi-slash me-1"></i> Offline</span>`;
      }
    });
  }

  // Atualiza status de sincronização
  function updateSyncStatus(state) {
    // Implementar indicador de sync se necessário
  }

  // ===========================
  // GERENCIAMENTO DE CACHE
  // ===========================

  // Salva dados acadêmicos
  async function saveAcademicData(type, data, metadata = {}) {
    try {
      const record = {
        id: type,
        type: type,
        data: data,
        metadata: metadata,
        timestamp: Date.now(),
        version: metadata.version || 1,
      };

      const transaction = db.transaction(
        [CONFIG.STORES.ACADEMIC_DATA],
        "readwrite"
      );
      const store = transaction.objectStore(CONFIG.STORES.ACADEMIC_DATA);
      await store.put(record);

      console.log(`✅ Dados salvos: ${type}`);
      return true;
    } catch (error) {
      console.error(`❌ Erro ao salvar ${type}:`, error);
      return false;
    }
  }

  // Carrega dados acadêmicos
  async function loadAcademicData(type) {
    try {
      const transaction = db.transaction(
        [CONFIG.STORES.ACADEMIC_DATA],
        "readonly"
      );
      const store = transaction.objectStore(CONFIG.STORES.ACADEMIC_DATA);
      const request = store.get(type);

      return new Promise((resolve, reject) => {
        request.onsuccess = () => {
          const result = request.result;
          if (result) {
            // Verificar se os dados não expiraram
            const age = Date.now() - result.timestamp;
            const maxAge =
              CONFIG.CACHE_DURATION[type.toUpperCase()] ||
              CONFIG.CACHE_DURATION.BOLETIM;

            if (age < maxAge) {
              console.log(`✅ Dados carregados do cache: ${type}`);
              resolve(result);
            } else {
              console.log(`⚠️ Dados expirados: ${type}`);
              resolve(null);
            }
          } else {
            resolve(null);
          }
        };
        request.onerror = () => reject(request.error);
      });
    } catch (error) {
      console.error(`❌ Erro ao carregar ${type}:`, error);
      return null;
    }
  }

  // Carrega dados com fallback
  async function loadDataWithFallback(type, apiEndpoint) {
    // Tentar buscar online primeiro se estiver conectado
    if (isOnline) {
      try {
        const response = await fetch(apiEndpoint);
        if (response.ok) {
          const data = await response.json();

          // Salvar no cache
          await saveAcademicData(type, data, {
            source: "api",
            url: apiEndpoint,
            version: 1,
          });

          return { data, source: "online", fresh: true };
        }
      } catch (error) {
        console.warn(`⚠️ Erro ao buscar online ${type}:`, error);
      }
    }

    // Buscar do cache offline
    const cached = await loadAcademicData(type);
    if (cached) {
      const age = Date.now() - cached.timestamp;
      return {
        data: cached.data,
        source: "cache",
        fresh: age < 60 * 60 * 1000, // Fresh se menos de 1 hora
        age: age,
      };
    }

    return null;
  }

  // Verifica disponibilidade de dados offline
  async function checkOfflineDataAvailability() {
    try {
      const transaction = db.transaction(
        [CONFIG.STORES.ACADEMIC_DATA],
        "readonly"
      );
      const store = transaction.objectStore(CONFIG.STORES.ACADEMIC_DATA);
      const request = store.count();

      return new Promise((resolve) => {
        request.onsuccess = () => resolve(request.result > 0);
        request.onerror = () => resolve(false);
      });
    } catch (error) {
      return false;
    }
  }

  // Obtém tempo do último sync
  async function getLastSyncTime() {
    try {
      const transaction = db.transaction(
        [CONFIG.STORES.APP_CONFIG],
        "readonly"
      );
      const store = transaction.objectStore(CONFIG.STORES.APP_CONFIG);
      const request = store.get("last_sync");

      return new Promise((resolve) => {
        request.onsuccess = () => {
          const result = request.result;
          resolve(result ? result.timestamp : null);
        };
        request.onerror = () => resolve(null);
      });
    } catch (error) {
      return null;
    }
  }

  // ===========================
  // UTILITÁRIOS
  // ===========================

  function getDataAge(timestamp) {
    const age = Date.now() - timestamp;
    const minutes = Math.floor(age / (1000 * 60));
    const hours = Math.floor(age / (1000 * 60 * 60));
    const days = Math.floor(age / (1000 * 60 * 60 * 24));

    if (days > 0) return `${days}d atrás`;
    if (hours > 0) return `${hours}h atrás`;
    if (minutes > 0) return `${minutes}min atrás`;
    return "agora";
  }

  // ===========================
  // EVENT HANDLERS
  // ===========================

  function handleOnline() {
    isOnline = true;
    console.log("🟢 Conectado à internet");

    // Tentar sincronizar dados
    setTimeout(() => {
      startSyncManager();
    }, 1000);
  }

  function handleOffline() {
    isOnline = false;
    console.log("🔴 Desconectado da internet");
  }

  function handleWindowFocus() {
    if (isOnline) {
      startSyncManager();
    }
  }

  function handleBeforeUnload() {
    if (syncTimer) clearInterval(syncTimer);
    if (visualTimer) clearInterval(visualTimer);
  }

  function handleServiceWorkerMessage(event) {
    const { type, data } = event.data || {};

    switch (type) {
      case "CACHE_UPDATED":
        console.log("📦 Cache atualizado pelo Service Worker");
        break;
      case "OFFLINE_READY":
        console.log("📱 App pronto para uso offline");
        break;
    }
  }

  // ===========================
  // SYNC MANAGER
  // ===========================

  function startSyncManager() {
    if (syncTimer) clearInterval(syncTimer);

    if (isOnline) {
      // Sync imediato
      performSync();

      // Sync periódico
      syncTimer = setInterval(performSync, CONFIG.SYNC_INTERVAL);
    }
  }

  async function performSync() {
    if (!isOnline) return;

    try {
      // Atualizar timestamp do último sync
      const transaction = db.transaction(
        [CONFIG.STORES.APP_CONFIG],
        "readwrite"
      );
      const store = transaction.objectStore(CONFIG.STORES.APP_CONFIG);
      await store.put({
        id: "last_sync",
        timestamp: Date.now(),
      });

      console.log("🔄 Sincronização realizada");
    } catch (error) {
      console.error("❌ Erro na sincronização:", error);
    }
  }

  // ===========================
  // API PÚBLICA
  // ===========================

  return {
    init,
    saveAcademicData,
    loadAcademicData,
    loadDataWithFallback,
    checkOfflineDataAvailability,
    getLastSyncTime,

    // Getters
    get isOnline() {
      return isOnline;
    },
    get hasDatabase() {
      return db !== null;
    },
  };
})();

// ===========================
// FUNÇÕES GLOBAIS PARA COMPATIBILIDADE
// ===========================

// Função global para carregar dados acadêmicos (compatibilidade)
async function loadAcademicData(type, endpoint, container, renderFunction) {
  try {
    // Mostrar loading
    showOfflineLoading(container, `Carregando ${type}...`);

    // Carregar dados com fallback
    const result = await OfflineCacheManager.loadDataWithFallback(
      type,
      `api_offline.php?action=${endpoint}`
    );

    if (result && result.data) {
      // Mostrar informações sobre os dados
      showDataInfo(container, result);

      // Renderizar dados
      renderFunction(result.data, container);
    } else {
      // Mostrar estado vazio
      showOfflineEmptyState(container, type);
    }
  } catch (error) {
    console.error(`Erro ao carregar ${type}:`, error);
    showOfflineError(container, type, error);
  }
}

// Mostra loading offline
function showOfflineLoading(container, message) {
  container.innerHTML = `
    <div class="offline-loading fade-in">
      <div class="spinner"></div>
      <div class="message">${message}</div>
      <div class="submessage">Verificando dados salvos...</div>
    </div>
  `;
}

// Mostra informações sobre os dados
function showDataInfo(container, result) {
  const { source, fresh, age } = result;

  let freshnessClass = "fresh";
  let freshnessText = "Dados atualizados";

  if (!fresh && age) {
    const hours = age / (1000 * 60 * 60);
    if (hours > 24) {
      freshnessClass = "old";
      freshnessText = "Dados antigos";
    } else if (hours > 6) {
      freshnessClass = "stale";
      freshnessText = "Dados um pouco antigos";
    }
  }

  const infoDiv = document.createElement("div");
  infoDiv.className = "offline-data-info slide-up";
  infoDiv.innerHTML = `
    <div class="info-item">
      <i class="fas fa-info-circle info-icon"></i>
      <span>Fonte: ${source === "online" ? "Servidor" : "Cache local"}</span>
      <span class="data-freshness ${freshnessClass}">
        <i class="fas fa-clock"></i>
        ${freshnessText}
      </span>
    </div>
    ${
      age
        ? `
      <div class="info-item">
        <i class="fas fa-history info-icon"></i>
        <span>Última atualização: ${getDataAge(Date.now() - age)}</span>
      </div>
    `
        : ""
    }
  `;

  container.insertAdjacentElement("afterbegin", infoDiv);
}

// Mostra estado vazio offline
function showOfflineEmptyState(container, type) {
  container.innerHTML = `
    <div class="offline-empty-state fade-in">
      <div class="icon">
        <i class="fas fa-cloud-download-alt"></i>
      </div>
      <h4>Dados não disponíveis offline</h4>
      <p>
        Não encontramos dados de ${type} salvos no seu dispositivo. 
        Conecte-se à internet para carregar e salvar os dados.
      </p>
      <button class="btn btn-primary" onclick="location.reload()">
        <i class="fas fa-refresh"></i>
        Tentar novamente
      </button>
    </div>
  `;
}

// Mostra erro offline
function showOfflineError(container, type, error) {
  container.innerHTML = `
    <div class="offline-empty-state fade-in">
      <div class="icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <h4>Erro ao carregar dados</h4>
      <p>
        Ocorreu um erro ao tentar carregar os dados de ${type}.
      </p>
      <button class="btn btn-warning" onclick="location.reload()">
        <i class="fas fa-refresh"></i>
        Tentar novamente
      </button>
    </div>
  `;
}

function getDataAge(age) {
  const minutes = Math.floor(age / (1000 * 60));
  const hours = Math.floor(age / (1000 * 60 * 60));
  const days = Math.floor(age / (1000 * 60 * 60 * 24));

  if (days > 0) return `${days} dia${days > 1 ? "s" : ""} atrás`;
  if (hours > 0) return `${hours} hora${hours > 1 ? "s" : ""} atrás`;
  if (minutes > 0) return `${minutes} minuto${minutes > 1 ? "s" : ""} atrás`;
  return "agora";
}

// ===========================
// AUTO-INICIALIZAÇÃO
// ===========================

// Inicializar quando o DOM estiver pronto
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    OfflineCacheManager.init();
  });
} else {
  OfflineCacheManager.init();
}
