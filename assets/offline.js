// Módulo otimizado para gerenciar armazenamento offline no SUPACO PWA
const OfflineManager = (() => {
  // Configurações do IndexedDB
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

  // Inicialização do módulo
  async function init() {
    try {
      db = await openDB();
      setupEventListeners();
      startPeriodicSync();
      console.log("SUPACO PWA: OfflineManager inicializado");
      return true;
    } catch (error) {
      console.error("SUPACO PWA: Erro ao inicializar OfflineManager:", error);
      return false;
    }
  }

  // Salva dados acadêmicos no IndexedDB
  async function saveData(id, data) {
    try {
      const db = await openDB();
      const tx = db.transaction(STORES.ACADEMIC_DATA, "readwrite");
      const store = tx.objectStore(STORES.ACADEMIC_DATA);

      await store.put({
        id,
        data,
        timestamp: Date.now(),
      });

      return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve(true);
        tx.onerror = (event) => reject(event.target.error);
      });
    } catch (error) {
      console.error(`Erro ao salvar dados ${id}:`, error);
      return false;
    }
  }

  // Obtém dados do IndexedDB
  async function getData(id) {
    try {
      const db = await openDB();
      const tx = db.transaction(STORES.ACADEMIC_DATA, "readonly");
      const store = tx.objectStore(STORES.ACADEMIC_DATA);

      return new Promise((resolve, reject) => {
        const request = store.get(id);

        request.onsuccess = () => {
          if (request.result) {
            resolve(request.result.data);
          } else {
            resolve(null);
          }
        };

        request.onerror = (event) => reject(event.target.error);
      });
    } catch (error) {
      console.error(`Erro ao buscar dados ${id}:`, error);
      return null;
    }
  }

  // Obtém dados salvos com timestamp
  async function getDataWithTimestamp(id) {
    try {
      const db = await openDB();
      const tx = db.transaction(STORES.ACADEMIC_DATA, "readonly");
      const store = tx.objectStore(STORES.ACADEMIC_DATA);

      return new Promise((resolve, reject) => {
        const request = store.get(id);

        request.onsuccess = () => resolve(request.result);
        request.onerror = (event) => reject(event.target.error);
      });
    } catch (error) {
      console.error(`Erro ao buscar dados com timestamp ${id}:`, error);
      return null;
    }
  }

  // Lista todos os dados salvos
  async function getAllData() {
    try {
      const db = await openDB();
      const tx = db.transaction(STORES.ACADEMIC_DATA, "readonly");
      const store = tx.objectStore(STORES.ACADEMIC_DATA);

      return new Promise((resolve, reject) => {
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (event) => reject(event.target.error);
      });
    } catch (error) {
      console.error("Erro ao listar todos os dados:", error);
      return [];
    }
  }

  // Adiciona uma requisição pendente à fila
  async function addPendingRequest(url, method, headers, body) {
    try {
      const db = await openDB();
      const tx = db.transaction(STORES.PENDING_REQUESTS, "readwrite");
      const store = tx.objectStore(STORES.PENDING_REQUESTS);

      await store.add({
        url,
        method,
        headers,
        body,
        createdAt: Date.now(),
      });

      return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve(true);
        tx.onerror = (event) => reject(event.target.error);
      });
    } catch (error) {
      console.error("Erro ao adicionar requisição pendente:", error);
      return false;
    }
  }

  // Verifica se há dados salvos
  async function hasData(id) {
    try {
      const data = await getData(id);
      return data !== null;
    } catch (error) {
      return false;
    }
  }

  // Retorna a interface pública
  return {
    saveData,
    getData,
    getDataWithTimestamp,
    getAllData,
    addPendingRequest,
    hasData,
  };
})();

// Função para verificar conexão e status offline
function checkOfflineStatus() {
  const offlineIndicator = document.getElementById("offlineIndicator");
  const syncButton = document.getElementById("syncButton");

  // Atualiza indicador de status
  if (!navigator.onLine) {
    // Estamos offline
    if (offlineIndicator) {
      offlineIndicator.style.display = "block";
    }
    if (syncButton) {
      syncButton.classList.add("disabled");
    }

    // Mostra notificação apenas uma vez
    if (!localStorage.getItem("offline-notified")) {
      showToast(
        "Você está offline. Alguns dados podem estar desatualizados.",
        "warning"
      );
      localStorage.setItem("offline-notified", "true");
    }
  } else {
    // Estamos online
    if (offlineIndicator) {
      offlineIndicator.style.display = "none";
    }
    if (syncButton) {
      syncButton.classList.remove("disabled");
    }

    // Reset da notificação offline
    localStorage.removeItem("offline-notified");

    // Registra para sincronização em segundo plano
    if ("serviceWorker" in navigator && "SyncManager" in window) {
      navigator.serviceWorker.ready
        .then((registration) => {
          return registration.sync.register("sync-dados-academicos");
        })
        .catch((err) => {
          console.error(
            "Erro ao registrar sincronização em segundo plano:",
            err
          );
        });
    }
  }
}

// Função para carregar dados (tenta API primeiro, senão usa cache)
async function loadAcademicData(
  endpoint,
  cacheId,
  targetElement,
  renderFunction
) {
  try {
    // Tenta carregar do servidor primeiro se estiver online
    if (navigator.onLine) {
      try {
        const response = await fetch(
          `/SUAP/api_offline.php?endpoint=${endpoint}`
        );

        if (response.ok) {
          const data = await response.json();

          // Salva no IndexedDB para uso offline
          await offlineStorage.saveData(cacheId, data.dados);

          // Renderiza os dados
          renderFunction(data.dados, targetElement);
          return;
        }
      } catch (error) {
        console.log(
          `Não foi possível obter dados do servidor: ${error.message}`
        );
        // Continua para carregar do cache
      }
    }

    // Tenta carregar do cache
    const cachedData = await offlineStorage.getData(cacheId);

    if (cachedData) {
      // Mostra notificação se estiver offline
      if (!navigator.onLine) {
        showToast(`Exibindo dados offline de ${endpoint}`, "info");
      }

      // Renderiza os dados do cache
      renderFunction(cachedData, targetElement);
    } else if (!navigator.onLine) {
      // Se estiver offline e não tiver cache
      if (targetElement) {
        targetElement.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-wifi-slash me-2"></i>
                        Você está offline e não há dados de ${endpoint} salvos para uso offline.
                    </div>
                `;
      }
    }
  } catch (error) {
    console.error(`Erro ao carregar dados de ${endpoint}:`, error);

    if (targetElement) {
      targetElement.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Erro ao carregar dados: ${error.message}
                </div>
            `;
    }
  }
}

// Função para forçar sincronização de todos os dados
async function syncAllData() {
  if (!navigator.onLine) {
    showToast("Não é possível sincronizar enquanto estiver offline", "warning");
    return false;
  }

  try {
    showToast("Sincronizando dados...", "info");

    // Chama o endpoint de atualização
    const response = await fetch(
      "/SUAP/api_offline.php?endpoint=atualizar_todos"
    );

    if (response.ok) {
      const result = await response.json();

      if (result.status === "success") {
        // Busca dados atualizados de boletim
        const boletimResponse = await fetch(
          "/SUAP/api_offline.php?endpoint=boletim"
        );
        const boletimData = await boletimResponse.json();
        await offlineStorage.saveData("boletim", boletimData.dados);

        // Busca dados atualizados de horários
        const horariosResponse = await fetch(
          "/SUAP/api_offline.php?endpoint=horarios"
        );
        const horariosData = await horariosResponse.json();
        await offlineStorage.saveData("horarios", horariosData.dados);

        // Busca dados do usuário
        const meusDadosResponse = await fetch(
          "/SUAP/api_offline.php?endpoint=meusdados"
        );
        const meusDadosData = await meusDadosResponse.json();
        await offlineStorage.saveData("meusDados", meusDadosData.dados);

        // Atualiza a página
        showToast("Dados atualizados com sucesso!", "success");
        setTimeout(() => {
          window.location.reload();
        }, 1500);

        return true;
      }
    } else {
      throw new Error("Erro ao sincronizar dados");
    }
  } catch (error) {
    console.error("Erro na sincronização:", error);
    showToast("Falha ao sincronizar dados. Tente novamente.", "danger");
    return false;
  }
}

// Evento para controlar mudanças na conexão
window.addEventListener("online", () => {
  checkOfflineStatus();
  showToast("Conexão restabelecida. Os dados serão sincronizados.", "success");
});

window.addEventListener("offline", () => {
  checkOfflineStatus();
  showToast("Você está offline. Usando dados salvos localmente.", "warning");
});

// Inicialização quando o documento estiver pronto
document.addEventListener("DOMContentLoaded", () => {
  // Verifica status offline
  checkOfflineStatus();

  // Configura botão de sincronização
  const syncButton = document.getElementById("syncButton");
  if (syncButton) {
    syncButton.addEventListener("click", () => {
      syncAllData();
    });
  }

  // Escuta mensagens do Service Worker
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.addEventListener("message", (event) => {
      if (event.data && event.data.type === "SYNC_COMPLETE") {
        showToast(event.data.message, "success");
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      }
    });
  }
});
