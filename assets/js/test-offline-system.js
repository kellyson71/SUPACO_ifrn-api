// Script de Teste para Sistema Offline - SUPACO
const OfflineTestSuite = (() => {
  
  // Função principal de teste
  async function runAllTests() {
    console.log('=== INICIANDO TESTES DO SISTEMA OFFLINE ===');
    
    try {
      await testCacheSystem();
      await testOfflineIndicator();
      await testDataPersistence();
      await testOnlineSync();
      
      console.log('=== TODOS OS TESTES CONCLUÍDOS ===');
      return true;
    } catch (error) {
      console.error('Erro nos testes:', error);
      return false;
    }
  }

  // Teste 1: Sistema de Cache
  async function testCacheSystem() {
    console.log('🧪 Teste 1: Sistema de Cache');
    
    if (typeof AppCacheManager === 'undefined') {
      console.error('❌ AppCacheManager não encontrado');
      return false;
    }

    // Testa salvamento de dados
    const testData = {
      meusDados: { nome_usual: 'Teste Usuário', matricula: '123456' },
      boletim: [{ disciplina: 'Teste' }],
      horarios: [{ sigla: 'TST' }],
      timestamp: Date.now()
    };

    const saved = await AppCacheManager.saveAppData(testData);
    console.log(saved ? '✅ Dados salvos no cache' : '❌ Falha ao salvar dados');

    // Testa carregamento de dados
    const loaded = await AppCacheManager.loadFromCache();
    console.log(loaded ? '✅ Dados carregados do cache' : '❌ Falha ao carregar dados');

    // Testa estatísticas
    const stats = AppCacheManager.getCacheStats();
    console.log('📊 Estatísticas do cache:', stats);

    return true;
  }

  // Teste 2: Indicador Offline
  function testOfflineIndicator() {
    console.log('🧪 Teste 2: Indicador Offline');
    
    // Simula modo offline
    Object.defineProperty(navigator, 'onLine', {
      writable: true,
      value: false
    });
    
    // Dispara evento offline
    window.dispatchEvent(new Event('offline'));
    
    // Verifica se indicador foi criado
    setTimeout(() => {
      const indicator = document.getElementById('offline-indicator');
      console.log(indicator ? '✅ Indicador offline criado' : '❌ Indicador offline não encontrado');
      
      // Simula modo online
      Object.defineProperty(navigator, 'onLine', {
        writable: true,
        value: true
      });
      
      window.dispatchEvent(new Event('online'));
      
      setTimeout(() => {
        console.log(indicator && indicator.style.display === 'none' ? 
          '✅ Indicador offline ocultado' : '❌ Indicador offline não foi ocultado');
      }, 100);
    }, 100);
    
    return true;
  }

  // Teste 3: Persistência de Dados
  async function testDataPersistence() {
    console.log('🧪 Teste 3: Persistência de Dados');
    
    // Limpa cache
    AppCacheManager.clearCache();
    
    // Salva dados básicos
    const basicData = AppCacheManager.getBasicAppData();
    await AppCacheManager.saveAppData(basicData);
    
    // Simula recarregamento da página
    const reloadedData = await AppCacheManager.getAppData();
    
    if (reloadedData && reloadedData.isBasic) {
      console.log('✅ Dados básicos persistidos corretamente');
    } else {
      console.log('❌ Falha na persistência de dados básicos');
    }
    
    return true;
  }

  // Teste 4: Sincronização Online
  function testOnlineSync() {
    console.log('🧪 Teste 4: Sincronização Online');
    
    // Simula conexão online
    Object.defineProperty(navigator, 'onLine', {
      writable: true,
      value: true
    });
    
    window.dispatchEvent(new Event('online'));
    
    console.log('✅ Evento online disparado');
    return true;
  }

  // Funções auxiliares para teste manual
  function simulateOffline() {
    console.log('📱 Simulando modo offline...');
    
    Object.defineProperty(navigator, 'onLine', {
      writable: true,
      value: false
    });
    
    window.dispatchEvent(new Event('offline'));
    
    // Mostra indicador manualmente se necessário
    if (typeof AppCacheManager !== 'undefined') {
      AppCacheManager.updateOfflineIndicator();
    }
  }

  function simulateOnline() {
    console.log('🌐 Simulando modo online...');
    
    Object.defineProperty(navigator, 'onLine', {
      writable: true,
      value: true
    });
    
    window.dispatchEvent(new Event('online'));
  }

  function clearAllCache() {
    console.log('🗑️ Limpando todo o cache...');
    
    if (typeof AppCacheManager !== 'undefined') {
      AppCacheManager.clearCache();
    }
    
    if (typeof LocalStorageManager !== 'undefined') {
      LocalStorageManager.clearAllData();
    }
    
    localStorage.clear();
    sessionStorage.clear();
    
    console.log('✅ Cache limpo completamente');
  }

  function showCacheInfo() {
    console.log('📊 Informações do Cache:');
    
    if (typeof AppCacheManager !== 'undefined') {
      const stats = AppCacheManager.getCacheStats();
      console.log('AppCacheManager:', stats);
    }
    
    if (typeof LocalStorageManager !== 'undefined') {
      console.log('LocalStorageManager disponível');
    }
    
    console.log('localStorage keys:', Object.keys(localStorage));
    console.log('sessionStorage keys:', Object.keys(sessionStorage));
    console.log('Status online:', navigator.onLine);
  }

  function forceBasicData() {
    console.log('🔧 Forçando dados básicos...');
    
    if (typeof AppCacheManager !== 'undefined') {
      const basicData = AppCacheManager.getBasicAppData();
      AppCacheManager.saveAppData(basicData);
      AppCacheManager.updateInterface(basicData);
      console.log('✅ Dados básicos aplicados');
    }
  }

  // Interface pública
  return {
    runAllTests,
    testCacheSystem,
    testOfflineIndicator,
    testDataPersistence,
    testOnlineSync,
    simulateOffline,
    simulateOnline,
    clearAllCache,
    showCacheInfo,
    forceBasicData
  };
})();

// Adiciona ao console global para fácil acesso
window.OfflineTestSuite = OfflineTestSuite;

// Comandos úteis para o console
console.log(`
=== COMANDOS DE TESTE SUPACO ===

// Executar todos os testes
OfflineTestSuite.runAllTests()

// Simular modo offline
OfflineTestSuite.simulateOffline()

// Simular modo online  
OfflineTestSuite.simulateOnline()

// Limpar todo o cache
OfflineTestSuite.clearAllCache()

// Mostrar informações do cache
OfflineTestSuite.showCacheInfo()

// Forçar dados básicos
OfflineTestSuite.forceBasicData()

// Testes individuais
OfflineTestSuite.testCacheSystem()
OfflineTestSuite.testOfflineIndicator()
OfflineTestSuite.testDataPersistence()
OfflineTestSuite.testOnlineSync()

=== FIM COMANDOS ===
`);
