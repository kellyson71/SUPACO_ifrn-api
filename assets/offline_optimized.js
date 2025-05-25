// SUPACO PWA - Gerenciador de Armazenamento Offline Otimizado
const OfflineManager = (() => {
  // Configurações
  const CONFIG = {
    DB_NAME: "supaco-db",
    DB_VERSION: 1,
    STORES: {
      ACADEMIC_DATA: "dados-academicos",
      PENDING_REQUESTS: "pending-requests",
      APP_STATE: "app-state",
    },
    SYNC_INTERVAL: 5 * 60 * 1000, // 5 minutos
    DATA_EXPIRY: 24 * 60 * 60 * 1000, // 24 horas
  };

  let db = null;
  let isOnline = navigator.onLine;
  let syncTimer = null;

  // Inicialização
  async function init() {
    try {
      db = await openDB();
      setupEventListeners();
      startPeriodicSync();
      updateConnectionStatus();
      console.log("SUPACO PWA: OfflineManager inicializado");
      return true;
    } catch (error) {
      console.error("SUPACO PWA: Erro ao inicializar OfflineManager:", error);
      return false;
    }
  }

  // Configuração de listeners de eventos
  function setupEventListeners() {
    window.addEventListener("online", handleOnlineStatus);
    window.addEventListener("offline", handleOfflineStatus);

    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.addEventListener("message", handleSWMessage);
    }

    window.addEventListener("focus", () => {
      if (isOnline) syncData();
    });
  }

  // Abre conexão com IndexedDB
  async function openDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(CONFIG.DB_NAME, CONFIG.DB_VERSION);

      request.onerror = () => reject(request.error);

      request.onupgradeneeded = (event) => {
        const database = event.target.result;

        if (!database.objectStoreNames.contains(CONFIG.STORES.ACADEMIC_DATA)) {
          database.createObjectStore(CONFIG.STORES.ACADEMIC_DATA, {
            keyPath: "id",
          });
        }

        if (
          !database.objectStoreNames.contains(CONFIG.STORES.PENDING_REQUESTS)
        ) {
          database.createObjectStore(CONFIG.STORES.PENDING_REQUESTS, {
            keyPath: "id",
            autoIncrement: true,
          });
        }

        if (!database.objectStoreNames.contains(CONFIG.STORES.APP_STATE)) {
          database.createObjectStore(CONFIG.STORES.APP_STATE, {
            keyPath: "key",
          });
        }
      };

      request.onsuccess = () => resolve(request.result);
    });
  }

  // Gerencia status online
  function handleOnlineStatus() {
    isOnline = true;
    updateConnectionStatus();
    syncData();
    showNotification("success", "Conexão restaurada - sincronizando dados...");
    console.log("SUPACO PWA: Conexão restaurada");
  }

  // Gerencia status offline
  function handleOfflineStatus() {
    isOnline = false;
    updateConnectionStatus();
    showNotification(
      "warning",
      "Você está offline - usando dados salvos localmente"
    );
    console.log("SUPACO PWA: Aplicação offline");
  }

  // Atualiza indicadores visuais de conexão
  function updateConnectionStatus() {
    const offlineAlerts = document.querySelectorAll(
      "#offlineAlert, .offline-indicator"
    );
    const onlineButtons = document.querySelectorAll(".online-only");

    offlineAlerts.forEach((alert) => {
      alert.classList.toggle("d-none", isOnline);
    });

    onlineButtons.forEach((button) => {
      button.disabled = !isOnline;
    });

    const statusElements = document.querySelectorAll(
      "[data-connection-status]"
    );
    statusElements.forEach((element) => {
      element.textContent = isOnline ? "Online" : "Offline";
      element.className = isOnline ? "text-success" : "text-warning";
    });

    // Atualiza ícone de conexão
    const connectionIcons = document.querySelectorAll(".connection-icon");
    connectionIcons.forEach((icon) => {
      icon.className = `fas ${
        isOnline ? "fa-wifi" : "fa-wifi-slash"
      } connection-icon`;
    });
  }

  // Processa mensagens do Service Worker
  function handleSWMessage(event) {
    const { data } = event;

    switch (data.type) {
      case "SYNC_DATA":
        saveData(data.endpoint, data.data);
        break;
      case "SYNC_COMPLETE":
        showNotification("success", data.message);
        break;
      default:
        console.log("SUPACO PWA: Mensagem SW:", data);
    }
  }

  // Salva dados no IndexedDB
  async function saveData(id, data) {
    if (!db) return false;

    try {
      const tx = db.transaction(CONFIG.STORES.ACADEMIC_DATA, "readwrite");
      const store = tx.objectStore(CONFIG.STORES.ACADEMIC_DATA);

      const record = {
        id,
        data,
        timestamp: Date.now(),
        synced: true,
      };

      await new Promise((resolve, reject) => {
        const request = store.put(record);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      });

      console.log(`SUPACO PWA: Dados ${id} salvos localmente`);
      return true;
    } catch (error) {
      console.error(`SUPACO PWA: Erro ao salvar ${id}:`, error);
      return false;
    }
  }

  // Recupera dados do IndexedDB
  async function getData(id) {
    if (!db) return null;

    try {
      const tx = db.transaction(CONFIG.STORES.ACADEMIC_DATA, "readonly");
      const store = tx.objectStore(CONFIG.STORES.ACADEMIC_DATA);

      const result = await new Promise((resolve, reject) => {
        const request = store.get(id);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });

      if (result && !isDataExpired(result.timestamp)) {
        return result.data;
      }

      // Se dados expiraram e estamos online, busca novos
      if (isOnline) {
        return await fetchFreshData(id);
      }

      // Retorna dados expirados se offline
      return result ? result.data : null;
    } catch (error) {
      console.error(`SUPACO PWA: Erro ao buscar ${id}:`, error);
      return null;
    }
  }

  // Verifica se dados expiraram
  function isDataExpired(timestamp) {
    return Date.now() - timestamp > CONFIG.DATA_EXPIRY;
  }

  // Busca dados frescos da API
  async function fetchFreshData(endpoint) {
    try {
      const response = await fetch(
        `/SUAP/api_offline.php?endpoint=${endpoint}`
      );
      if (response.ok) {
        const data = await response.json();
        await saveData(endpoint, data.dados || data);
        return data.dados || data;
      }
    } catch (error) {
      console.error(`SUPACO PWA: Erro ao buscar ${endpoint}:`, error);
    }
    return null;
  }

  // Carrega dados com fallback inteligente
  async function loadData(endpoint, forceRefresh = false) {
    try {
      // Se forçou refresh ou está online, tenta buscar novos dados
      if (forceRefresh || isOnline) {
        const freshData = await fetchFreshData(endpoint);
        if (freshData) return freshData;
      }

      // Fallback para dados em cache
      const cachedData = await getData(endpoint);
      if (cachedData) {
        if (!isOnline) {
          showNotification("info", `Exibindo dados offline de ${endpoint}`);
        }
        return cachedData;
      }

      return null;
    } catch (error) {
      console.error(`SUPACO PWA: Erro ao carregar ${endpoint}:`, error);
      return null;
    }
  }

  // Sincronização de dados
  async function syncData() {
    if (!isOnline || !db) return;

    try {
      console.log("SUPACO PWA: Iniciando sincronização");

      if (
        "serviceWorker" in navigator &&
        "sync" in window.ServiceWorkerRegistration.prototype
      ) {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register("sync-dados-academicos");
      } else {
        await manualSync();
      }
    } catch (error) {
      console.error("SUPACO PWA: Erro na sincronização:", error);
    }
  }

  // Sincronização manual (fallback)
  async function manualSync() {
    const endpoints = ["boletim", "horarios", "meusdados"];

    for (const endpoint of endpoints) {
      await fetchFreshData(endpoint);
    }

    showNotification("success", "Dados sincronizados com sucesso");
  }

  // Inicia sincronização periódica
  function startPeriodicSync() {
    if (syncTimer) clearInterval(syncTimer);

    syncTimer = setInterval(() => {
      if (isOnline) syncData();
    }, CONFIG.SYNC_INTERVAL);
  }

  // Exibe notificação
  function showNotification(type, message) {
    if (typeof Toastify !== "undefined") {
      Toastify({
        text: message,
        backgroundColor: getNotificationColor(type),
        duration: 3000,
      }).showToast();
    } else if (typeof showToast === "function") {
      showToast(message, type);
    } else {
      console.log(`SUPACO PWA: ${message}`);
    }
  }

  // Cores para notificações
  function getNotificationColor(type) {
    const colors = {
      success: "#28a745",
      error: "#dc3545",
      warning: "#ffc107",
      info: "#17a2b8",
    };
    return colors[type] || colors.info;
  }

  // Força sincronização
  function forceSync() {
    return syncData();
  }

  // Limpa dados expirados
  async function clearExpiredData() {
    if (!db) return;

    try {
      const tx = db.transaction(CONFIG.STORES.ACADEMIC_DATA, "readwrite");
      const store = tx.objectStore(CONFIG.STORES.ACADEMIC_DATA);

      const allData = await new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });

      for (const item of allData) {
        if (isDataExpired(item.timestamp)) {
          await new Promise((resolve, reject) => {
            const request = store.delete(item.id);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
          });
        }
      }

      console.log("SUPACO PWA: Dados expirados removidos");
    } catch (error) {
      console.error("SUPACO PWA: Erro ao limpar dados:", error);
    }
  }

  // Obtém estatísticas de armazenamento
  async function getStorageStats() {
    if (!db) return null;

    try {
      const tx = db.transaction(CONFIG.STORES.ACADEMIC_DATA, "readonly");
      const store = tx.objectStore(CONFIG.STORES.ACADEMIC_DATA);

      const allData = await new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });

      return {
        totalItems: allData.length,
        lastSync: Math.max(...allData.map((item) => item.timestamp || 0)),
        isOnline,
        dataSize: JSON.stringify(allData).length,
        hasExpiredData: allData.some((item) => isDataExpired(item.timestamp)),
      };
    } catch (error) {
      console.error("SUPACO PWA: Erro ao obter estatísticas:", error);
      return null;
    }
  }

  // Interface pública
  return {
    init,
    saveData,
    getData,
    loadData,
    syncData: forceSync,
    clearExpiredData,
    getStorageStats,
    get isOnline() {
      return isOnline;
    },
  };
})();

// Auto-inicialização quando DOM estiver pronto
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", OfflineManager.init);
} else {
  OfflineManager.init();
}

// Exporta para uso global
window.OfflineManager = OfflineManager;
