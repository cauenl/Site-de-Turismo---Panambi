// Inicialização dos carrosséis dinâmicos
document.addEventListener('DOMContentLoaded', function() {
    // Configuração padrão do Swiper
    const swiperConfig = {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 30,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 40,
            },
        },
        effect: 'slide',
        speed: 600,
        grabCursor: true,
        centeredSlides: false,
    };

    // Inicializa todos os carrosséis na página
    const swiperContainers = document.querySelectorAll('.swiper');
    swiperContainers.forEach(container => {
        new Swiper(container, swiperConfig);
    });

    // Função para adicionar animações aos cards
    function animateCards() {
        const cards = document.querySelectorAll('.card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }

    // Inicializa animações
    animateCards();

    // Função para confirmar exclusão
    window.confirmarExclusao = function(nome, id, tipo) {
        const confirmacao = confirm(`Tem certeza que deseja excluir "${nome}"?`);
        if (confirmacao) {
            const currentPage = window.location.pathname.split('/').pop();
            window.location.href = `${currentPage}?excluir=${id}`;
        }
    };

    // Adiciona efeitos de hover suaves
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.05)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Lazy loading para imagens
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // Smooth scroll para links internos
    const internalLinks = document.querySelectorAll('a[href^="#"]');
    internalLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Adiciona loading state aos botões de ação
    const actionButtons = document.querySelectorAll('.btn[href*="excluir"], .btn[href*="editar"]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('btn-delete') || confirm('Tem certeza que deseja excluir este item?')) {
                this.innerHTML = '<span class="loading"></span> Processando...';
                this.style.pointerEvents = 'none';
            }
        });
    });
});

// Função para recarregar carrossel quando novos itens são adicionados
function reloadCarousel() {
    const swiperContainers = document.querySelectorAll('.swiper');
    swiperContainers.forEach(container => {
        if (container.swiper) {
            container.swiper.update();
            container.swiper.slideTo(0);
        }
    });
}

// Função para mostrar notificações
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '300px';
    notification.style.animation = 'slideInRight 0.5s ease';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 500);
    }, 3000);
}

// Adiciona animações CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;
document.head.appendChild(style);

