<?php
// Inclui a configuração do banco PRIMEIRO para ter a variável $conexao
require_once(__DIR__ . '/php/config.php');
// Inclui as funções
require_once(__DIR__ . '/php/functions.php');

// Verifica se a conexão com o banco foi estabelecida em config.php
if (!$conexao || $conexao->connect_error) {
    error_log("Erro fatal em eventos.php: Falha ao conectar ao banco de dados. Detalhes: " . ($conexao ? $conexao->connect_error : 
        'Variável de conexão não encontrada
    '));
    die("Erro ao carregar a página de eventos. Por favor, tente mais tarde.");
}

// Inicia a sessão para verificação de login
session_start();
$usuarioLogado = isset($_SESSION["usuario_id"]) || isset($_SESSION["tipo"]);
$ehEmpresa = isset($_SESSION["tipo"]) && ($_SESSION["tipo"] == "empresa" || $_SESSION["tipo"] == "admin");
$usuario_id = isset($_SESSION["usuario_id"]) ? $_SESSION["usuario_id"] : null;

$mensagem_exclusao =
    ''; // Para feedback de exclusão

// Verifica se há uma solicitação de exclusão e se o usuário tem permissão
if (isset($_GET["excluir"]) && is_numeric($_GET["excluir"]) && $usuarioLogado) {
    $id_para_excluir = (int)$_GET["excluir"];
    $base_path = __DIR__; // Diretório atual

    // Tenta excluir o evento, passando a conexão, o caminho base e o ID do usuário
    if (excluirEvento($conexao, $id_para_excluir, $base_path, $usuario_id)) {
        // Redireciona para a mesma página sem o parâmetro GET para evitar reenvio
        header("Location: eventos.php?excluido=1");
        exit;
    } else {
        // Define uma mensagem de erro se a exclusão falhar
        $mensagem_exclusao = "<div class=\"alert alert-danger\">Erro ao excluir o evento. Você só pode excluir eventos que criou.</div>";
        // Log do erro já é feito dentro da função excluirEvento
    }
}

// Obtém a lista de eventos do banco de dados, passando a conexão
$eventos = listarEventos($conexao);

// Extrai categorias únicas para o filtro
$categoriasUnicas = [];
if (!empty($eventos)) {
    // Garante que 'categoria' existe e não é nula antes de adicionar
    $categoriasValidas = array_filter(array_column($eventos, 'categoria'), function($cat) {
        return !is_null($cat) && $cat !== '';
    });
    $categoriasUnicas = array_unique($categoriasValidas);
    sort($categoriasUnicas); // Ordena alfabeticamente
}

// Fecha a conexão com o banco de dados, pois não será mais usada neste script.
$conexao->close();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/eventos.css"> <!-- Link para CSS externo -->
    <title>Eventos Cadastrados</title>
 
</head>

<body>

    <div class="background">

        <!-- Header e Navegação (mantidos como estavam no zip) -->
        <header class="header-red-bar">
            <div class="header-content">
                <div class="header-contact">
                    <a href="#">+55 55 8402-7519</a>
                    <a href="#">coord.turismo@panambi.rs.gov.br</a>
                </div>
                <div class="header-social">
                    <!-- SVGs omitidos por brevidade, usar os do zip -->
                    <a href="#" target="_blank" class="facebook">Facebook</a>
                    <a href="#" target="_blank" class="instagram">Instagram</a>
                    <a href="#" target="_blank" class="whatsapp">WhatsApp</a>
                </div>
            </div>
        </header>
        <nav class="nav-bar">
            <div class="header-bottom-nav">
                <div class="header-logo">
                    <a href="./index.php"><img src="./img/escudo2.png" alt="logo"></a>
                </div>
                <ul class="menu">
                    <li class="nav-item"><a href="./index.php">Início</a></li>
                    <li class="nav-item"><a href="#">A Cidade</a></li>
                    <li class="nav-item"><a href="#">Experiências</a></li>
                    <li class="nav-item"><a href="./eventos.php">Eventos</a></li>
                    <li class="nav-item"><a href="#">Roteiros</a></li>
                    <li class="nav-item"><a href="#">Contato</a></li>
                </ul>
                <div class="mobile-menu-icon">
                    <!-- Usar o botão/SVG do zip -->
                    <button onclick="menuShow()" class="mobile-btn">☰</button> <!-- Exemplo de ícone -->
                </div>
            </div>
        </nav>
        <div class="mobile-menu overlay-menu">
            <!-- Conteúdo do menu mobile (usar o do zip) -->
            <button class="close-menu" onclick="menuShow()">×</button> <!-- Botão fechar -->
            <ul>
                 <li><a href="./index.php">Início</a></li>
                 <li><a href="#">A Cidade</a></li>
                 <li><a href="#">Experiências</a></li>
                 <li><a href="./eventos.php">Eventos</a></li>
                 <li><a href="#">Roteiros</a></li>
                 <li><a href="#">Contato</a></li>
            </ul>
        </div>
        <!-- Fim Header e Navegação -->

        <!-- Conteúdo Principal -->
        <main class="events-container">
            <section>
                <div class="title">
                    <h1>Eventos Cadastrados</h1>
                </div>

                <?php
                // Exibe mensagem de sucesso se o parâmetro GET 'excluido' estiver presente
                if (isset($_GET['excluido']) && $_GET['excluido'] == '1'): ?>
                <div class="alert alert-success">
                    Evento excluído com sucesso!
                </div>
                <?php endif; ?>

                <?php
                // Exibe mensagem de sucesso se o parâmetro GET 'sucesso' estiver presente (vindo do formEvent.php)
                if (isset($_GET['sucesso']) && $_GET['sucesso'] == '1'): ?>
                <div class="alert alert-success">
                    Evento cadastrado com sucesso!
                </div>
                <?php endif; ?>

                <?php
                // Exibe mensagem de erro de exclusão, se houver
                echo $mensagem_exclusao;
                ?>

                <!-- Botão para adicionar evento (mostrado apenas se logado como empresa/admin) -->
                <?php if ($ehEmpresa): ?>
                <a href="formEvent.php" class="add-event-btn">Cadastrar Novo Evento</a>
                <?php endif; ?>

                <!-- Filtro de Categorias -->
                <?php if (!empty($categoriasUnicas)): ?>
                <div class="filter-container">
                    <button class="filter-btn active" data-filter="all">Todos</button>
                    <?php foreach ($categoriasUnicas as $categoria): ?>
                    <button class="filter-btn" data-filter="<?php echo htmlspecialchars($categoria); ?>">
                        <?php echo htmlspecialchars($categoria); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <!-- Fim Filtro de Categorias -->

                <!-- Container onde os cards de evento serão exibidos -->
                <div class="cards-container" id="cardsContainer">
                    <?php if (empty($eventos)): ?>
                    <p class="no-events-message" id="noEventsMessageInitial">Nenhum evento cadastrado no momento.</p>
                    <?php else: ?>
                    <?php foreach ($eventos as $evento): ?>
                    <div class="card" data-categoria="<?php echo htmlspecialchars($evento['categoria']); ?>">
                        <!-- Imagem do Evento -->
                        <img src="./<?php echo htmlspecialchars($evento['imagem']); ?>"
                            alt="Imagem do Evento: <?php echo htmlspecialchars($evento['nome_evento']); ?>">
                        <div class="card-content">
                            <h2 class="card-title"><?php echo htmlspecialchars($evento['nome_evento']); ?></h2>

                            <p class="card-text"><strong>Data:</strong>
                                <?php echo formatarData($evento['data_inicial']); ?>
                                <?php if ($evento['data_inicial'] != $evento['data_final']): ?>
                                a <?php echo formatarData($evento['data_final']); ?>
                                <?php endif; ?>
                                &nbsp;|&nbsp;
                                <strong>Hora:</strong> <?php echo formatarHora($evento['horario']); ?>
                            </p>

                            <p class="card-text"><strong>Local:</strong> <?php echo !empty($evento['local']) ? htmlspecialchars($evento['local']) : 'Local não informado'; ?></p>
                            
                            <p class="card-text"><strong>Valor:</strong> 
                                <?php 
                                if (isset($evento['pago']) && $evento['pago'] == 1 && isset($evento['valor']) && (float)$evento['valor'] > 0) {
                                    echo 'R$ ' . htmlspecialchars(number_format((float)$evento['valor'], 2, ',', '.'));
                                } else {
                                    echo 'Gratuito';
                                }
                                ?>
                            </p>
                            
                            <p class="card-text"><strong>Categoria:</strong> <?php echo htmlspecialchars($evento['categoria']); ?></p>

                            <p class="card-text"><strong>Descrição/Infos:</strong><br>
                                <?php echo nl2br(htmlspecialchars($evento['info_descricao'])); // nl2br para quebras de linha ?>
                            </p>

                            <!-- Ações (excluir) - mostradas apenas se logado como empresa/admin -->
                            <?php if ($ehEmpresa): ?>
                            <div class="card-actions">
                                <a href="eventos.php?excluir=<?php echo $evento['id']; ?>"
                                    class="card-btn-delete"
                                    onclick="return confirm('Tem certeza que deseja excluir este evento?\n\n<?php echo htmlspecialchars(addslashes($evento['nome_evento']), ENT_QUOTES); ?>');">Excluir</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- Mensagem para quando o filtro não retorna resultados -->
                    <p class="filter-results-message" id="filterResultMessage">Nenhum evento encontrado para esta categoria.</p>
                </div>

            </section>
        </main>
        <!-- Fim Conteúdo Principal -->

    </div> <!-- Fim .background -->

    <script src="./js/main.js"></script> <!-- Assumindo que main.js existe e pode ter outras funções -->

    <script>
        // Função menuShow() - Mantida para compatibilidade, idealmente estaria em main.js
        function menuShow() {
            let menuMobile = document.querySelector(".mobile-menu");
            if (menuMobile) {
                menuMobile.classList.toggle("open");
                // Opcional: Travar scroll do body quando menu aberto
                document.body.style.overflow = menuMobile.classList.contains("open") ? 'hidden' : '';
            }
        }
        // Código para fechar o menu mobile pelo botão X
        const closeMenuBtn = document.querySelector(".close-menu");
        if (closeMenuBtn) {
            closeMenuBtn.addEventListener("click", menuShow); // Reutiliza a função para fechar
        }

        // Lógica do Filtro de Categorias
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const cardsContainer = document.getElementById('cardsContainer');
            const cards = cardsContainer.querySelectorAll('.card');
            const noEventsMessageInitial = document.getElementById('noEventsMessageInitial');
            const filterResultMessage = document.getElementById('filterResultMessage');

            // Função para atualizar a exibição das mensagens
            function updateMessages(visibleCardCount) {
                const totalCards = cards.length;
                const currentFilter = document.querySelector('.filter-btn.active').getAttribute('data-filter');

                // Esconde todas as mensagens inicialmente
                if (noEventsMessageInitial) noEventsMessageInitial.style.display = 'none';
                if (filterResultMessage) filterResultMessage.style.display = 'none';

                if (totalCards === 0) {
                    // Se não há eventos NENHUM cadastrado
                    if (noEventsMessageInitial) noEventsMessageInitial.style.display = 'block';
                } else if (visibleCardCount === 0 && currentFilter !== 'all') {
                    // Se há eventos, mas NENHUM corresponde ao filtro ATUAL
                    if (filterResultMessage) {
                         const categoryName = document.querySelector(`.filter-btn[data-filter="${currentFilter}"]`).textContent;
                         filterResultMessage.textContent = 'Nenhum evento encontrado para a categoria "' + categoryName + '".';
                         filterResultMessage.style.display = 'block';
                    }
                } else if (visibleCardCount === 0 && currentFilter === 'all') {
                     // Caso estranho: tem cards no HTML mas nenhum é visível com filtro "Todos"?
                     // Isso não deveria acontecer se a lógica de filtro estiver correta.
                     // Poderia mostrar a mensagem inicial por segurança.
                     if (noEventsMessageInitial) noEventsMessageInitial.style.display = 'block';
                }
                // Se visibleCardCount > 0, nenhuma mensagem de "nenhum evento" é mostrada.
            }

            // Adiciona listeners aos botões de filtro
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Atualiza classe ativa nos botões
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.getAttribute('data-filter');
                    let visibleCardCount = 0;

                    // Filtra os cards
                    cards.forEach(card => {
                        const cardCategory = card.getAttribute('data-categoria');
                        if (filter === 'all' || cardCategory === filter) {
                            card.style.display = 'flex'; // Mostra o card
                            visibleCardCount++;
                        } else {
                            card.style.display = 'none'; // Esconde o card
                        }
                    });

                    // Atualiza as mensagens com base nos resultados do filtro
                    updateMessages(visibleCardCount);
                });
            });

            // Estado inicial das mensagens ao carregar a página
            updateMessages(cards.length); // Mostra a mensagem inicial se não houver cards
        });

    </script>

</body>

</html>

