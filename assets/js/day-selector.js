/**
 * Funcionalidades do seletor de dia para visualização de aulas
 */
document.addEventListener("DOMContentLoaded", function () {
  // Encontra o seletor de dias
  const daySelector = document.getElementById("daySelector");

  if (daySelector) {
    // Adiciona o listener para mudança no seletor
    daySelector.addEventListener("change", function () {
      // Obter a data selecionada
      const selectedDate = this.value;

      if (selectedDate) {
        // Redirecionar para a página com a data selecionada
        window.location.href = `?data=${selectedDate}`;
      }
    });

    // Marca a opção atual como selecionada
    const urlParams = new URLSearchParams(window.location.search);
    const currentDate = urlParams.get("data");

    if (currentDate) {
      // Seleciona a data atual no seletor
      const options = daySelector.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].value === currentDate) {
          daySelector.selectedIndex = i;
          break;
        }
      }
    }
  }

  // Adicionar tooltips para indicadores de feriados
  const holidayBadges = document.querySelectorAll(".holiday-badge");
  if (typeof bootstrap !== "undefined" && holidayBadges) {
    holidayBadges.forEach((badge) => {
      new bootstrap.Tooltip(badge);
    });
  }
});
