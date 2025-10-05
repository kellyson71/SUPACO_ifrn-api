const AppCacheManager = (() => {
  const CONFIG = {
    CACHE_KEY: 'supaco_full_app_cache',
    DATA_EXPIRY: 7 * 24 * 60 * 60 * 1000, // 7 dias
    SYNC_INTERVAL: 5 * 60 * 1000, // 5 minutos
    VERSION: '1.0.0'
  };

  let isOnline = navigator.onLine;
  let lastSyncTime = null;
  let syncTimer = null;

  // Inicialização
  async function init() {
    try {
      setupEventListeners();
      startPeriodicSync();
      updateOfflineIndicator();
      
      // Carrega dados do cache se offline
      if (!isOnline) {
        await loadFromCache();
      }
      
      console.log('SUPACO: AppCacheManager inicializado');
      return true;
    } catch (error) {
      console.error('SUPACO: Erro ao inicializar AppCacheManager:', error);
      return false;
    }
  }

  // Configuração de listeners
  function setupEventListeners() {
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    
    // Detecta mudanças de foco da janela
    window.addEventListener('focus', () => {
      if (isOnline) {
        syncData();
      }
    });
  }

  // Gerencia status online
  function handleOnline() {
    isOnline = true;
    updateOfflineIndicator();
    syncData();
    console.log('SUPACO: Conexão restaurada');
  }

  // Gerencia status offline
  function handleOffline() {
    isOnline = false;
    updateOfflineIndicator();
    console.log('SUPACO: Aplicação offline');
  }

  // Salva todos os dados da aplicação no cache
  async function saveAppData(data) {
    try {
      const cacheData = {
        ...data,
        timestamp: Date.now(),
        version: CONFIG.VERSION,
        lastSync: Date.now()
      };

      localStorage.setItem(CONFIG.CACHE_KEY, JSON.stringify(cacheData));
      lastSyncTime = Date.now();
      
      console.log('SUPACO: Dados da aplicação salvos no cache');
      return true;
    } catch (error) {
      console.error('SUPACO: Erro ao salvar dados no cache:', error);
      return false;
    }
  }

  // Carrega dados do cache
  async function loadFromCache() {
    try {
      const cachedData = localStorage.getItem(CONFIG.CACHE_KEY);
      if (!cachedData) {
        console.log('SUPACO: Nenhum dado em cache encontrado');
        return null;
      }

      const data = JSON.parse(cachedData);
      
      if (isDataExpired(data.timestamp)) {
        console.log('SUPACO: Dados do cache expirados');
        localStorage.removeItem(CONFIG.CACHE_KEY);
        return null;
      }

      console.log('SUPACO: Dados carregados do cache');
      return data;
    } catch (error) {
      console.error('SUPACO: Erro ao carregar dados do cache:', error);
      return null;
    }
  }

  // Verifica se dados expiraram
  function isDataExpired(timestamp) {
    return Date.now() - timestamp > CONFIG.DATA_EXPIRY;
  }

  // Atualiza indicador offline
  function updateOfflineIndicator() {
    const profileImage = document.querySelector('.profile-image');
    const profilePlaceholder = document.querySelector('.profile-placeholder');
    const offlineIndicator = document.getElementById('offline-indicator');
    
    if (!isOnline) {
      // Cria indicador offline se não existir
      if (!offlineIndicator) {
        const indicator = document.createElement('div');
        indicator.id = 'offline-indicator';
        indicator.className = 'offline-indicator';
        indicator.innerHTML = `
          <div class="offline-icon">
            <i class="fas fa-wifi-slash"></i>
          </div>
          <div class="offline-text">
            <span>Offline</span>
            <small>Dados salvos</small>
          </div>
        `;
        
        // Insere após o placeholder da foto
        if (profilePlaceholder) {
          profilePlaceholder.parentNode.insertBefore(indicator, profilePlaceholder.nextSibling);
        } else if (profileImage) {
          profileImage.parentNode.insertBefore(indicator, profileImage.nextSibling);
        }
      }
      
      // Esconde foto do usuário quando offline
      if (profileImage) {
        profileImage.style.display = 'none';
      }
      if (profilePlaceholder) {
        profilePlaceholder.style.display = 'none';
      }
      
      if (offlineIndicator) {
        offlineIndicator.style.display = 'block';
      }
    } else {
      // Mostra foto do usuário quando online
      if (profileImage) {
        profileImage.style.display = 'block';
      }
      if (profilePlaceholder) {
        profilePlaceholder.style.display = 'block';
      }
      
      if (offlineIndicator) {
        offlineIndicator.style.display = 'none';
      }
    }
  }

  // Sincroniza dados quando online
  async function syncData() {
    if (!isOnline) return;
    
    try {
      console.log('SUPACO: Iniciando sincronização de dados...');
      
      // Aqui você pode implementar a lógica de sincronização
      // Por exemplo, buscar dados atualizados da API
      
      lastSyncTime = Date.now();
      console.log('SUPACO: Sincronização concluída');
    } catch (error) {
      console.error('SUPACO: Erro na sincronização:', error);
    }
  }

  // Inicia sincronização periódica
  function startPeriodicSync() {
    if (syncTimer) clearInterval(syncTimer);
    
    syncTimer = setInterval(() => {
      if (isOnline) {
        syncData();
      }
    }, CONFIG.SYNC_INTERVAL);
  }

  // Obtém dados da aplicação (cache ou API)
  async function getAppData(forceRefresh = false) {
    try {
      // Se online e forçando refresh, busca dados novos
      if (isOnline && forceRefresh) {
        // Aqui você implementaria a busca de dados da API
        console.log('SUPACO: Buscando dados atualizados da API...');
      }
      
      // Tenta carregar do cache
      const cachedData = await loadFromCache();
      if (cachedData) {
        return cachedData;
      }
      
      // Se não há cache, sempre retorna dados básicos
      console.log('SUPACO: Nenhum cache encontrado, usando dados básicos');
      const basicData = getBasicAppData();
      
      // Salva dados básicos no cache para uso futuro
      await saveAppData(basicData);
      
      return basicData;
    } catch (error) {
      console.error('SUPACO: Erro ao obter dados da aplicação:', error);
      return getBasicAppData();
    }
  }

  // Dados básicos da aplicação
  function getBasicAppData() {
    return {
      meusDados: {
        nome_usual: 'Usuário SUPACO',
        matricula: '2024000000',
        vinculo: {
          curso: 'Curso não informado'
        },
        url_foto_150x200: 'assets/images/perfil.png',
        tipo_usuario: 'aluno'
      },
      boletim: [
        {
          disciplina: 'Exemplo - Disciplina 1',
          nota_etapa_1: { nota: null },
          nota_etapa_2: { nota: null },
          percentual_carga_horaria_frequentada: 100,
          numero_faltas: 0,
          carga_horaria: 80,
          carga_horaria_cumprida: 40
        },
        {
          disciplina: 'Exemplo - Disciplina 2',
          nota_etapa_1: { nota: null },
          nota_etapa_2: { nota: null },
          percentual_carga_horaria_frequentada: 100,
          numero_faltas: 0,
          carga_horaria: 80,
          carga_horaria_cumprida: 40
        }
      ],
      horarios: [
        {
          sigla: 'EX1',
          descricao: 'Exemplo - Disciplina 1',
          horarios_de_aula: '2M12,4M34',
          locais_de_aula: ['Sala 101']
        },
        {
          sigla: 'EX2',
          descricao: 'Exemplo - Disciplina 2',
          horarios_de_aula: '3T12,5T34',
          locais_de_aula: ['Lab 01']
        }
      ],
      anoLetivo: new Date().getFullYear(),
      periodoLetivo: new Date().getMonth() < 6 ? 1 : 2,
      timestamp: Date.now(),
      version: CONFIG.VERSION,
      isBasic: true
    };
  }

  // Atualiza interface com dados
  function updateInterface(data) {
    try {
      if (!data) return;

      // Atualiza informações do usuário
      if (data.meusDados) {
        updateUserInfo(data.meusDados);
      }

      // Atualiza período letivo
      if (data.anoLetivo && data.periodoLetivo) {
        updatePeriodInfo(data.anoLetivo, data.periodoLetivo);
      }

      console.log('SUPACO: Interface atualizada com dados do cache');
    } catch (error) {
      console.error('SUPACO: Erro ao atualizar interface:', error);
    }
  }

  // Atualiza informações do usuário
  function updateUserInfo(userData) {
    const elements = {
      nome: document.querySelector('.main-title'),
      matricula: document.querySelector('.registration-code'),
      curso: document.querySelector('.course-info span'),
      foto: document.querySelector('.profile-image')
    };

    if (elements.nome && userData.nome_usual) {
      elements.nome.textContent = userData.nome_usual;
    }

    if (elements.matricula && userData.matricula) {
      elements.matricula.textContent = userData.matricula;
    }

    if (elements.curso && userData.vinculo && userData.vinculo.curso) {
      elements.curso.textContent = userData.vinculo.curso;
    }

    if (elements.foto && userData.url_foto_150x200) {
      elements.foto.src = userData.url_foto_150x200;
    }
  }

  // Atualiza informações do período
  function updatePeriodInfo(ano, periodo) {
    const periodoElement = document.querySelector('.period-badge');
    if (periodoElement) {
      periodoElement.textContent = `${ano}.${periodo}`;
    }
  }

  // Limpa cache
  function clearCache() {
    try {
      localStorage.removeItem(CONFIG.CACHE_KEY);
      console.log('SUPACO: Cache limpo');
      return true;
    } catch (error) {
      console.error('SUPACO: Erro ao limpar cache:', error);
      return false;
    }
  }

  // Obtém estatísticas do cache
  function getCacheStats() {
    try {
      const cachedData = localStorage.getItem(CONFIG.CACHE_KEY);
      if (!cachedData) {
        return {
          hasData: false,
          size: 0,
          lastSync: null,
          isExpired: false
        };
      }

      const data = JSON.parse(cachedData);
      return {
        hasData: true,
        size: cachedData.length,
        lastSync: data.lastSync,
        timestamp: data.timestamp,
        isExpired: isDataExpired(data.timestamp),
        version: data.version
      };
    } catch (error) {
      console.error('SUPACO: Erro ao obter estatísticas do cache:', error);
      return null;
    }
  }

  // Interface pública
  return {
    init,
    saveAppData,
    loadFromCache,
    getAppData,
    updateInterface,
    clearCache,
    getCacheStats,
    get isOnline() {
      return isOnline;
    },
    get lastSyncTime() {
      return lastSyncTime;
    }
  };
})();

// Auto-inicialização
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', AppCacheManager.init);
} else {
  AppCacheManager.init();
}

// Exporta para uso global
window.AppCacheManager = AppCacheManager;
