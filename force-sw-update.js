// Script para forçar atualização do Service Worker
console.log('🔄 Forçando atualização do Service Worker...');

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        console.log('📋 Service Workers registrados:', registrations.length);
        
        // Remove todos os service workers antigos
        registrations.forEach(function(registration) {
            console.log('🗑️ Removendo Service Worker antigo:', registration.scope);
            registration.unregister();
        });
        
        // Aguarda um pouco e registra novamente
        setTimeout(function() {
            navigator.serviceWorker.register('sw.js')
                .then(function(registration) {
                    console.log('✅ Service Worker registrado com sucesso!');
                    console.log('📡 Scope:', registration.scope);
                    
                    // Força atualização
                    registration.update();
                    
                    // Aguarda instalação
                    registration.addEventListener('updatefound', function() {
                        console.log('🔄 Atualização encontrada, instalando...');
                        
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', function() {
                            if (newWorker.state === 'installed') {
                                if (navigator.serviceWorker.controller) {
                                    console.log('🎉 Nova versão instalada! Recarregue a página.');
                                    // Opcional: recarregar automaticamente
                                    // window.location.reload();
                                } else {
                                    console.log('✅ Service Worker instalado pela primeira vez!');
                                }
                            }
                        });
                    });
                })
                .catch(function(error) {
                    console.error('❌ Erro ao registrar Service Worker:', error);
                });
        }, 1000);
    });
} else {
    console.log('❌ Service Worker não suportado neste navegador');
}

// Função para limpar cache
function clearAllCache() {
    console.log('🧹 Limpando todos os caches...');
    
    if ('caches' in window) {
        caches.keys().then(function(cacheNames) {
            cacheNames.forEach(function(cacheName) {
                console.log('🗑️ Removendo cache:', cacheName);
                caches.delete(cacheName);
            });
            console.log('✅ Todos os caches foram limpos!');
        });
    }
    
    // Limpa localStorage
    if (typeof localStorage !== 'undefined') {
        localStorage.clear();
        console.log('✅ localStorage limpo!');
    }
    
    // Limpa sessionStorage
    if (typeof sessionStorage !== 'undefined') {
        sessionStorage.clear();
        console.log('✅ sessionStorage limpo!');
    }
}

// Adiciona função global
window.forceSWUpdate = function() {
    console.log('🔄 Executando atualização forçada...');
    clearAllCache();
    
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            registrations.forEach(function(registration) {
                registration.unregister();
            });
            
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        });
    } else {
        window.location.reload();
    }
};

console.log('💡 Use forceSWUpdate() para forçar atualização completa');
console.log('💡 Use clearAllCache() para limpar apenas os caches');
