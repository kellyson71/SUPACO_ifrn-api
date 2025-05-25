// Sistema de notificações para SUPACO
document.addEventListener("DOMContentLoaded", function () {
  // Verificar se a biblioteca Toastify está disponível
  if (typeof Toastify !== "undefined") {
    // Mostrar notificação de boas-vindas
    setTimeout(() => {
      showToast(
        "Bem-vindo(a) ao SUPACO! Dados acadêmicos carregados.",
        "success"
      );
    }, 800);
  }

  // Animações para itens do boletim
  const disciplinaItems = document.querySelectorAll(".disciplina-item");
  disciplinaItems.forEach((item, index) => {
    item.classList.add("boletim-item");
    item.style.animationDelay = `${0.1 + index * 0.05}s`;
  });
});

/**
 * Exibe uma notificação toast
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo de notificação ('success', 'warning', 'danger', 'info')
 * @param {number} duration - Duração em milissegundos
 */
function showToast(message, type = "success", duration = 5000) {
  const bgColors = {
    success: "linear-gradient(to right, #10b981, #059669)",
    warning: "linear-gradient(to right, #f59e0b, #d97706)",
    danger: "linear-gradient(to right, #ef4444, #dc2626)",
    info: "linear-gradient(to right, #06b6d4, #0891b2)",
  };

  const icons = {
    success: '<i class="fas fa-check-circle me-2"></i>',
    warning: '<i class="fas fa-exclamation-circle me-2"></i>',
    danger: '<i class="fas fa-exclamation-triangle me-2"></i>',
    info: '<i class="fas fa-info-circle me-2"></i>',
  };
  Toastify({
    text: icons[type] + message,
    duration: duration,
    gravity: "top",
    position: "right",
    className: "toast-notification",
    escapeMarkup: false,
    style: {
      background: bgColors[type],
      boxShadow: "0 3px 10px rgba(0,0,0,0.1)",
      borderRadius: "8px",
      padding: "12px 16px",
      fontSize: "14px",
    },
  }).showToast();
}
