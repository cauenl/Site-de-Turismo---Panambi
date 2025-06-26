<?php
// Converter arquivo HTML para PHP e adicionar suporte para exibição dinâmica de locais turísticos
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/functions_localturistico.php';
require_once __DIR__ . '/php/admin_functions.php';

session_start();
$usuarioLogado = isset($_SESSION["usuario_id"]);
$usuario_id = $_SESSION["usuario_id"] ?? null;

// Verifica se há uma solicitação de exclusão
$mensagem_exclusao = '';
if (isset($_GET["excluir"]) && is_numeric($_GET["excluir"]) && $usuarioLogado) {
    $id_para_excluir = (int)$_GET["excluir"];
    if (excluirLocalTuristico($conexao, $id_para_excluir, $usuario_id)) {
        header("Location: gastronomia.php?excluido=1");
        exit;
    } else {
        $mensagem_exclusao = "<div class=\"alert alert-danger\">Erro ao excluir o local. Você só pode excluir locais que criou.</div>";
    }
}

// Verifica se há mensagem de sucesso
$sucesso = isset($_GET['sucesso']) ? (int)$_GET['sucesso'] : 0;
$excluido = isset($_GET['excluido']) ? (int)$_GET['excluido'] : 0;
$mensagem_sucesso = '';
if ($sucesso === 1) {
    $mensagem_sucesso = "Local turístico cadastrado com sucesso!";
} elseif ($excluido === 1) {
    $mensagem_sucesso = "Local turístico excluído com sucesso!";
}

// Busca locais turísticos da categoria Gastronomia
$locais_turisticos = [];
if ($conexao && !$conexao->connect_error) {
    $locais_turisticos = listarLocaisTuristicosPorCategoria($conexao, 'Gastronomia');
}

$locais = listarLocaisTuristicosAprovados($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastronomia - Portal de Turismo Panambi</title>
    <link rel="stylesheet" href="./css/carousel.css">
    <link rel="stylesheet" href="./css/swiper-bundle.min.css">
</head>

<body>
    <header>
        <div class="logo">
            <a href="./index.php"><img src="./img/escudo2.png" alt="Logo Portal Turismo Panambi"></a>
        </div>
        <nav>
            <ul>
                <li><a href="./index.php">Início</a></li>
                <li><a href="./turismo.php">Turismo</a></li>
                <li><a href="./TurismoRural.php">Turismo Rural</a></li>
                <li><a href="./hospedagem.php">Hospedagem</a></li>
                <li><a href="./gastronomia.php" class="active">Gastronomia</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($mensagem_sucesso); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_exclusao)): ?>
        <?php echo $mensagem_exclusao; ?>
        <?php endif; ?>

        <h1>Gastronomia em Panambi</h1>

        <!-- Carrossel de Gastronomia -->
        <div class="carousel-container">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <!-- Cards estáticos existentes -->
                    <!-- <div class="swiper-slide">
                        <div class="card">
                            <img src="./img/bendito_cafe.jpg" alt="Bendito Café">
                            <div class="card-content">
                                <h2>Bendito Café</h2>
                                <p>Cafeteria aconchegante com ambiente agradável e café de qualidade.</p>
                                <a href="#" class="btn">Saiba Mais</a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="card">
                            <img src="./img/kasekuchen.jpg" alt="Padaria Nina">
                            <div class="card-content">
                                <h2>Padaria Nina</h2>
                                <p>Padaria tradicional com produtos artesanais e receitas alemãs.</p>
                                <a href="#" class="btn">Saiba Mais</a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="card">
                            <img src="./img/xis_gaucho.jpg" alt="Yes Burger">
                            <div class="card-content">
                                <h2>Yes Burger</h2>
                                <p>Hamburgueria com lanches artesanais e ambiente descontraído.</p>
                                <a href="#" class="btn">Saiba Mais</a>
                            </div>
                        </div>
                    </div> -->

                    <!-- Cards dinâmicos dos locais turísticos cadastrados -->
                    <?php foreach ($locais_turisticos as $local): ?>
                    <div class="swiper-slide">
                        <div class="card">
                            <!-- Imagem dinâmica se existir, senão imagem padrão -->
                            <img src="<?php echo !empty($local['imagem']) ? htmlspecialchars($local['imagem']) : './img/padaria_nina.jpg'; ?>" alt="<?php echo htmlspecialchars($local['nome']); ?>">
                            <div class="card-content">
                                <h2><?php echo htmlspecialchars($local['nome']); ?></h2>
                                <p><?php echo htmlspecialchars($local['descricao'] ?? 'Local gastronômico em Panambi'); ?></p>
                                <div class="contact-info">
                                    <p><strong>Endereço:</strong> <?php echo htmlspecialchars($local['endereco'] ?? ''); ?></p>
                                    <p><strong>Contato:</strong> <?php echo htmlspecialchars($local['telefone'] ?? ''); ?></p>
                                </div>
                                <a href="#" class="btn">Saiba Mais</a>
                                <?php if ($usuarioLogado && isset($local['usuario_id']) && $local['usuario_id'] == $usuario_id): ?>
                                <a href="gastronomia.php?excluir=<?php echo isset($local['idLocal']) ? htmlspecialchars($local['idLocal']) : ''; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Tem certeza que deseja excluir este local?')">
                                   Excluir
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Navegação do carrossel -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                
                <!-- Paginação -->
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <?php if (empty($locais_turisticos)): ?>
        <div style="text-align: center; margin: 3rem 0; color: #ccc;">
            <p>Nenhum local gastronômico cadastrado ainda.</p>
            <p>Seja o primeiro a cadastrar um local!</p>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Portal de Turismo de Panambi. Todos os direitos reservados.</p>
    </footer>

    <script src="./js/swiper-bundle.min.js"></script>
    <script src="./js/carousel.js"></script>
</body>

</html>

