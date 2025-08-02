// Inicialização do ScrollReveal
const sr = ScrollReveal({
    reset: true, // As animações ocorrerão sempre que o elemento entrar na viewport
    distance: '50px',
    duration: 1000,
    easing: 'ease-in-out',
});

// Animações para a Navegação e Hero
sr.reveal('.navbar-item', {
    origin: 'top',
    distance: '20px',
    interval: 100
});

sr.reveal('.logo, .logo-hover', {
    origin: 'left',
    distance: '20px'
});

sr.reveal('.hero h1', {
    origin: 'left',
    distance: '50px'
});

sr.reveal('.text p', {
    origin: 'right',
    distance: '50px',
    delay: 200
});

sr.reveal('.hero-buttons .btn', {
    origin: 'bottom',
    distance: '30px',
    interval: 100
});

sr.reveal('.seta-img', {
    duration: 1000,
    easing: 'ease-in-out',
    reset: 'true',
    origin: 'bottom'
})

// Animações para Recursos Principais
sr.reveal('.recursos-img', {
    origin: 'bottom',
    distance: '50px',
    opacity: 0,        // Começa com opacidade 0 (invisível)
    duration: 2000,     // Duração da animação (em milissegundos)
    easing: 'ease-in-out',
    reset: true,            // Não anima novamente ao rolar para fora e voltar
    afterReveal: function (el) {
        el.style.opacity = 1; // Define a opacidade para 1 após a revelação
    }
});

sr.reveal('.feature-item', {
    origin: 'bottom',
    distance: '50px'
});

// Animações para Testemunhos
sr.reveal('.testimonial-item, .testimonials h2', {
    origin: 'bottom',
    distance: '50px',
});

// Animações para Cards de Planos e Preços
sr.reveal('.card-price, .pricing h2', {
    origin: 'right',
    distance: '50px',
    interval: 200
});

// Animações para Perguntas Frequentes/ titulo de testemunho
sr.reveal('.faq-item, .faq-content h2', {
    origin: 'bottom',
    distance: '30px',
    interval: 100
});

// Animações para a Seção de Contato
sr.reveal('.contact h2, .contact p', {
    origin: 'bottom',
    distance: '30px',
    interval: 100
});


// JavaScript para a funcionalidade de transição suave nas respostas do FAQ
const faqItems = document.querySelectorAll('.faq-item');

faqItems.forEach((item) => {
    const question = item.querySelector('.question');
    const answer = item.querySelector('p');

    question.addEventListener('click', () => {
        // Fechar outros itens abertos
        faqItems.forEach((el) => {
            if (el !== item) {
                el.classList.remove('active');
                el.querySelector('p').style.maxHeight = null;
            }
        });

        // Alternar o item clicado
        item.classList.toggle('active');

        if (item.classList.contains('active')) {
            // Expande a resposta
            answer.style.maxHeight = answer.scrollHeight + 'px';
        } else {
            // Recolhe a resposta
            answer.style.maxHeight = null;
        }
    });
});


// Animações para o Footer
sr.reveal('.logo-details', {
    origin: 'top',
    distance: '30px'
});

sr.reveal('.box', {
    origin: 'left',
    distance: '50px',
    interval: 200
});

sr.reveal('.copyright_text', {
    origin: 'top',
    distance: '30px'
});


sr.reveal('.wrapper', {
    origin: 'rigth',
    distance: '50px'
});


sr.reveal('.wrapper', {
    origin: 'rigth',
    distance: '50px'
});



//scroll store
ScrollReveal().reveal('#savedTable', {
    origin: 'left', // A animação vem de baixo
    distance: '50px', // Distância de movimento
    duration: 800, // Duração da animação em ms
    delay: 100, // Atraso entre os elementos
    interval: 100, // Intervalo de animação entre as linhas da tabela
    easing: 'ease-in-out'
});


// Initialize ScrollReveal
ScrollReveal().reveal('.title', {
    distance: '50px',
    origin: 'bottom',
    duration: 1000,
    delay: 300
});

ScrollReveal().reveal('.tab-content', {
    distance: '30px',
    origin: 'top',
    duration: 800,
    delay: 200,
    interval: 100
});

ScrollReveal().reveal('.field-group', {
    distance: '30px',
    origin: 'left',
    duration: 800,
    delay: 400,
    interval: 150
});

ScrollReveal().reveal('.options-container, .actions', {
    distance: '30px',
    origin: 'right',
    duration: 800,
    delay: 500,
    interval: 150
});

ScrollReveal().reveal('.slider-container', {
    distance: '30px',
    origin: 'bottom',
    duration: 900,
    delay: 600
});

ScrollReveal().reveal('.copy-button', {
    distance: '50px',
    origin: 'bottom',
    duration: 1000,
    delay: 700
});