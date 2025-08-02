

document.addEventListener("DOMContentLoaded", () => {
    const card = document.querySelector(".card");
    const spinner = document.querySelector(".SPINNER");

    // Aplicar animações
    setTimeout(() => {
        card.style.opacity = "1";
        card.style.transform = "translateX(0)"; // Remove o deslocamento da esquerda

        spinner.style.opacity = "1";
        spinner.style.transform = "translateY(0)"; // Remove o deslocamento do bottom
    }); // Atraso para um efeito suave
});


//redireciona auto pro topo
document.addEventListener('DOMContentLoaded', () => {
    // Redirecionar o scroll para o topo ao recarregar a página
    window.scrollTo(0, 0);
});