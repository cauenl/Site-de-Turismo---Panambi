// ===== [ MENU MOBILE ] ===== //
function initMobileMenu() {
    const menuMobile = document.querySelector(".mobile-menu");
    const navList = document.querySelector(".nav-list"); // Supondo que você tenha uma lista de navegação
    const mobileMenuButton = document.querySelector(".mobile-btn"); // Botão que abre o menu
    const closeMenuButton = document.querySelector(".close-menu"); // Botão que fecha o menu

    const toggleMenu = () => {
        if (menuMobile) {
            menuMobile.classList.toggle("open");
            // Opcional: Animação ou alteração no botão
            // mobileMenuButton.classList.toggle("active");
        }
    };

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener("click", toggleMenu);
    }

    if (closeMenuButton) {
        closeMenuButton.addEventListener("click", toggleMenu);
    }

    // Opcional: Fechar o menu ao clicar em um item da lista
    // if (navList) {
    //     navList.addEventListener("click", (event) => {
    //         if (event.target.tagName === "A" && menuMobile.classList.contains("open")) {
    //             toggleMenu();
    //         }
    //     });
    // }
}

// ===== [ LOGIN/CADASTRO TOGGLE ] ===== //
// (Mantido como estava no arquivo original)
function initLoginToggle() {
    const formSignin = document.querySelector("#signin");
    const formSignup = document.querySelector("#signup");
    const btnColor = document.querySelector(".btnColor");
    const container = document.querySelector(".container"); // Adicionado para ajustar altura

    const btnSignin = document.querySelector("#btnSignin");
    const btnSignup = document.querySelector("#btnSignup");

    if (
        formSignin &&
        formSignup &&
        btnColor &&
        btnSignin &&
        btnSignup &&
        container
    ) {
        btnSignin.addEventListener("click", () => {
            btnColor.style.left = "0px";
            formSignin.style.left = "25px";
            formSignup.style.left = "450px";
            container.style.height = "420px"; // Ajustar altura conforme necessário
        });

        btnSignup.addEventListener("click", () => {
            btnColor.style.left = "108px"; // Ajustar valor conforme necessário
            formSignin.style.left = "-450px";
            formSignup.style.left = "25px";
            container.style.height = "550px"; // Ajustar altura conforme necessário
        });
    }
}

// ===== [ SLIDER ] ===== //
// (Mantido como estava no arquivo original, assumindo que Swiper JS está incluído)
function initSlider() {
    // Verifica se o elemento e a biblioteca Swiper existem
    if (
        typeof Swiper !== "undefined" &&
        document.querySelector(".slide-content")
    ) {
        try {
            const swiper = new Swiper(".slide-content", {
                slidesPerView: 4,
                spaceBetween: 30,
                loop: true,
                // centerSlides: true, // centerSlides geralmente não funciona bem com loop e múltiplos slides visíveis
                grabCursor: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    0: { slidesPerView: 1, spaceBetween: 10 },
                    520: { slidesPerView: 2, spaceBetween: 20 },
                    768: { slidesPerView: 3, spaceBetween: 25 }, // Ajuste breakpoint se necessário
                    950: { slidesPerView: 4, spaceBetween: 30 }, // Ajuste breakpoint se necessário
                },
            });
        } catch (error) {
            console.error("Erro ao inicializar o Swiper Slider:", error);
        }
    } else if (!document.querySelector(".slide-content")) {
        // console.log("Elemento .slide-content não encontrado para o slider.");
    } else {
        console.warn("Biblioteca Swiper não carregada ou não encontrada.");
    }
}

// ===== [ EVENTOS - CRUD via localStorage ] ===== //
// REMOVIDO - Toda a função initEventos() foi removida pois a gestão de eventos
// agora é feita pelo PHP e banco de dados.
/*
function initEventos() {
    // ... (código antigo removido) ...
}
*/

// ===== [ INICIALIZAÇÃO ] ===== //
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM carregado. Inicializando scripts...");
    initMobileMenu();
    initLoginToggle();
    initSlider();
    // A chamada initEventos(); foi REMOVIDA.
    console.log("Scripts inicializados.");
});

// Função menuShow() global (se não estiver dentro de initMobileMenu)
// Se você chama onclick="menuShow()" no HTML, ela precisa estar no escopo global.
// A versão em initMobileMenu usa event listeners, que é mais recomendado.
/* 
function menuShow() {
    let menuMobile = document.querySelector(".mobile-menu");
    if (menuMobile) {
         menuMobile.classList.toggle("open");
    }
}
*/
