const LocalStorageManager = (() => {
  const CONFIG = {
    STORAGE_KEY: 'supaco_user_data',
    DATA_EXPIRY: 30 * 24 * 60 * 60 * 1000, // 30 dias
    BASIC_DATA_KEY: 'supaco_basic_data'
  };

  function saveUserData(userData) {
    try {
      const dataToSave = {
        ...userData,
        timestamp: Date.now(),
        isBasic: false
      };
      
      localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(dataToSave));
      console.log('SUPACO: Dados do usuário salvos no localStorage');
      return true;
    } catch (error) {
      console.error('SUPACO: Erro ao salvar dados no localStorage:', error);
      return false;
    }
  }

  function getUserData() {
    try {
      const storedData = localStorage.getItem(CONFIG.STORAGE_KEY);
      if (!storedData) return null;

      const userData = JSON.parse(storedData);
      
      if (isDataExpired(userData.timestamp)) {
        console.log('SUPACO: Dados expirados, removendo do localStorage');
        localStorage.removeItem(CONFIG.STORAGE_KEY);
        return null;
      }

      return userData;
    } catch (error) {
      console.error('SUPACO: Erro ao recuperar dados do localStorage:', error);
      return null;
    }
  }

  function saveBasicData(basicData) {
    try {
      const dataToSave = {
        ...basicData,
        timestamp: Date.now(),
        isBasic: true
      };
      
      localStorage.setItem(CONFIG.BASIC_DATA_KEY, JSON.stringify(dataToSave));
      console.log('SUPACO: Dados básicos salvos no localStorage');
      return true;
    } catch (error) {
      console.error('SUPACO: Erro ao salvar dados básicos:', error);
      return false;
    }
  }

  function getBasicData() {
    try {
      const storedData = localStorage.getItem(CONFIG.BASIC_DATA_KEY);
      if (!storedData) return null;

      const basicData = JSON.parse(storedData);
      
      if (isDataExpired(basicData.timestamp)) {
        console.log('SUPACO: Dados básicos expirados, removendo do localStorage');
        localStorage.removeItem(CONFIG.BASIC_DATA_KEY);
        return null;
      }

      return basicData;
    } catch (error) {
      console.error('SUPACO: Erro ao recuperar dados básicos:', error);
      return null;
    }
  }

  function isDataExpired(timestamp) {
    return Date.now() - timestamp > CONFIG.DATA_EXPIRY;
  }

  function clearAllData() {
    try {
      localStorage.removeItem(CONFIG.STORAGE_KEY);
      localStorage.removeItem(CONFIG.BASIC_DATA_KEY);
      console.log('SUPACO: Todos os dados removidos do localStorage');
      return true;
    } catch (error) {
      console.error('SUPACO: Erro ao limpar dados:', error);
      return false;
    }
  }

  function hasValidData() {
    const userData = getUserData();
    const basicData = getBasicData();
    return userData !== null || basicData !== null;
  }

  function getAvailableData() {
    const userData = getUserData();
    if (userData) {
      return {
        data: userData,
        isBasic: false,
        source: 'localStorage (completo)'
      };
    }

    const basicData = getBasicData();
    if (basicData) {
      return {
        data: basicData,
        isBasic: true,
        source: 'localStorage (básico)'
      };
    }

    return null;
  }

  function showBasicDataWarning() {
    const existingWarning = document.getElementById('basic-data-warning');
    if (existingWarning) {
      existingWarning.remove();
    }

    const warning = document.createElement('div');
    warning.id = 'basic-data-warning';
    warning.className = 'alert alert-warning alert-dismissible fade show';
    warning.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    `;
    
    warning.innerHTML = `
      <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <div>
          <strong>Dados Básicos</strong><br>
          <small>Você está usando apenas os dados básicos. Faça o login novamente para atualizar os dados.</small>
        </div>
        <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
      </div>
      <div class="mt-2">
        <a href="login.php" class="btn btn-sm btn-primary">
          <i class="fas fa-sign-in-alt me-1"></i>
          Fazer Login
        </a>
      </div>
    `;

    document.body.appendChild(warning);

    setTimeout(() => {
      if (warning.parentElement) {
        warning.remove();
      }
    }, 10000);
  }

  function createBasicUserData() {
    return {
      nome_usual: 'Usuário SUPACO',
      matricula: '2024000000',
      vinculo: {
        curso: 'Curso não informado'
      },
      url_foto_150x200: 'assets/images/perfil.png',
      tipo_usuario: 'aluno'
    };
  }

  function createBasicBoletimData() {
    return [
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
    ];
  }

  function createBasicHorariosData() {
    return [
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
    ];
  }

  function initializeBasicData() {
    const basicData = {
      meusDados: createBasicUserData(),
      boletim: createBasicBoletimData(),
      horarios: createBasicHorariosData(),
      anoLetivo: new Date().getFullYear(),
      periodoLetivo: new Date().getMonth() < 6 ? 1 : 2
    };

    saveBasicData(basicData);
    return basicData;
  }

  return {
    saveUserData,
    getUserData,
    saveBasicData,
    getBasicData,
    clearAllData,
    hasValidData,
    getAvailableData,
    showBasicDataWarning,
    initializeBasicData,
    createBasicUserData,
    createBasicBoletimData,
    createBasicHorariosData
  };
})();

window.LocalStorageManager = LocalStorageManager;
