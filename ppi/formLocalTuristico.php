<?php
// Inicia a sessão para verificação de login
session_start();

$usuario_id = $_SESSION['usuario_id'] ?? null;  // pega o ID da sessão

// Redireciona para login se não estiver logado
if (!$usuario_id) {
    header("Location: login.php");
    exit;
}

// Inclui a configuração do banco PRIMEIRO para ter a variável $conexao
require_once(__DIR__ . '/php/config.php');
// Inclui as funções
require_once(__DIR__ . '/php/functions.php');
// Inclui as funções de local
require_once(__DIR__ . '/php/functions_local.php');
// Inclui as funções de local turístico
require_once(__DIR__ . '/php/functions_localturistico.php');

// Verifica se a conexão com o banco foi estabelecida em config.php
if (!$conexao || $conexao->connect_error) {
    error_log("Erro fatal em formLocalTuristico.php: Falha ao conectar ao banco de dados. Detalhes: " . ($conexao ? $conexao->connect_error : 'Variável de conexão não encontrada'));
    die("Erro crítico: Não foi possível conectar ao banco de dados. Por favor, tente mais tarde ou contate o administrador.");
}

$erros = [];
$mensagem_sucesso = '';

// Inicializa $dados com todos os campos esperados pelo formulário
$dados = [
    'nome' => '',
    'descricao' => '',
    'endereco' => '',
    'categoria' => '',
    'tipo' => '',
    'dias_fechado' => '',
    'nome_contato' => '',
    'email_contato' => '',
    'telefone_contato' => ''
];

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta dados do formulário
    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'descricao' => $_POST['descricao'] ?? '',
        'endereco' => $_POST['endereco'] ?? '',
        'categoria' => $_POST['categoria'] ?? '',
        'tipo' => $_POST['tipo'] ?? '',
        'dias_fechado' => $_POST['dias_fechado'] ?? '',
        'nome_contato' => $_POST['nome_contato'] ?? '',
        'email_contato' => $_POST['email_contato'] ?? '',
        'telefone_contato' => $_POST['telefone_contato'] ?? ''
    ];

    $imagem_relativa = null;
    // Validação do upload da imagem (opcional)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
        $resultado_upload = uploadImagem($_FILES['imagem'], __DIR__);
        if (isset($resultado_upload['erro'])) {
            $erros['imagem'] = $resultado_upload['erro'];
        } else {
            $imagem_relativa = $resultado_upload['caminho_relativo'];
        }
    }

    // Validação básica
    if (empty($dados['nome'])) $erros['nome'] = "Nome do local é obrigatório";
    if (empty($dados['endereco'])) $erros['endereco'] = "Endereço é obrigatório";
    if (empty($dados['categoria'])) $erros['categoria'] = "Categoria é obrigatória";
    if (empty($dados['tipo'])) $erros['tipo'] = "Tipo é obrigatório";
    if (empty($dados['nome_contato'])) $erros['nome_contato'] = "Nome do contato é obrigatório";
    if (empty($dados['email_contato'])) $erros['email_contato'] = "E-mail do contato é obrigatório";
    if (empty($dados['telefone_contato'])) $erros['telefone_contato'] = "Telefone do contato é obrigatório";

    // Se não houver erros, processa o cadastro
    if (empty($erros)) {
        // Adicionar contato
        $id_contato = adicionarContato(
            $conexao, 
            $dados['nome_contato'], 
            $dados['email_contato'], 
            $dados['telefone_contato']
        );

        if ($id_contato) {
            // Adicionar local turístico (agora com imagem)
            $id_local = adicionarLocalTuristico(
                $conexao, 
                [
                    'nome' => $dados['nome'],
                    'descricao' => $dados['descricao'],
                    'endereco' => $dados['endereco'],
                    'categoria' => $dados['categoria'],
                    'tipo' => $dados['tipo'],
                    'dias_fechado' => $dados['dias_fechado'],
                    'imagem' => $imagem_relativa // novo campo
                ],
                $usuario_id,
                $id_contato
            );

            if ($id_local) {
                $mensagem_sucesso = "Local turístico cadastrado com sucesso!";
                
                // Redireciona para a página da categoria correspondente
                $pagina_categoria = obterPaginaCategoria($dados['categoria']);
                header("Location: $pagina_categoria?sucesso=1");
                exit;
            } else {
                $erros['geral'] = "Erro ao cadastrar local turístico. Por favor, tente novamente.";
            }
        } else {
            $erros['geral'] = "Erro ao cadastrar contato. Por favor, tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./css/formEvent.css">
    <title>Cadastrar Local Turístico</title>
    <style>
        .error-message {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .form-error {
            border: 1px solid red !important;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            text-align: center;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        /* Estilo para textarea */
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box; /* Para incluir padding e border no tamanho total */
            min-height: 80px; /* Altura mínima */
        }
        textarea.form-error {
             border: 1px solid red !important;
        }
    </style>
</head>

<body>
    <div class="img">
        <img src="./img/escudo2.png" alt="Logo">
    </div>

    <div class="container">
        <section class="header">
            <h1>Cadastrar Local Turístico</h1>
        </section>

        <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($mensagem_sucesso); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($erros['geral'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($erros['geral']); ?>
        </div>
        <?php endif; ?>

        <form id="form" class="form" method="POST" enctype="multipart/form-data" novalidate>
            <!-- Nome do Local -->
            <div class="form-content">
                <label for="nome">Nome do Local</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Restaurante Bom Sabor"
                    value="<?php echo htmlspecialchars($dados['nome']); ?>"
                    class="<?php echo isset($erros['nome']) ? 'form-error' : ''; ?>" required />
                <?php if (isset($erros['nome'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['nome']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Descrição -->
            <div class="form-content">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" placeholder="Descreva o local turístico..."
                    class="<?php echo isset($erros['descricao']) ? 'form-error' : ''; ?>"><?php echo htmlspecialchars($dados['descricao']); ?></textarea>
                <?php if (isset($erros['descricao'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['descricao']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Endereço -->
            <div class="form-content">
                <label for="endereco">Endereço</label>
                <input type="text" id="endereco" name="endereco" placeholder="Ex: Rua das Flores, 123"
                    value="<?php echo htmlspecialchars($dados['endereco']); ?>"
                    class="<?php echo isset($erros['endereco']) ? 'form-error' : ''; ?>" required />
                <?php if (isset($erros['endereco'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['endereco']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Categoria e Tipo -->
            <div class="form-row">
                <div class="form-content">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="<?php echo isset($erros['categoria']) ? 'form-error' : ''; ?>" required>
                        <option value="" <?php echo empty($dados['categoria']) ? 'selected' : ''; ?>>-- Selecione --</option>
                        <option value="Gastronomia" <?php echo ($dados['categoria'] === 'Gastronomia') ? 'selected' : ''; ?>>Gastronomia</option>
                        <option value="Hospedagem" <?php echo ($dados['categoria'] === 'Hospedagem') ? 'selected' : ''; ?>>Hospedagem</option>
                        <option value="Ponto Turístico" <?php echo ($dados['categoria'] === 'Ponto Turístico') ? 'selected' : ''; ?>>Ponto Turístico</option>
                        <option value="Local Rural" <?php echo ($dados['categoria'] === 'Local Rural') ? 'selected' : ''; ?>>Local Rural</option>
                    </select>
                    <?php if (isset($erros['categoria'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['categoria']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-content">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="<?php echo isset($erros['tipo']) ? 'form-error' : ''; ?>" required>
                        <option value="" <?php echo empty($dados['tipo']) ? 'selected' : ''; ?>>-- Selecione --</option>
                        <option value="privado" <?php echo ($dados['tipo'] === 'privado') ? 'selected' : ''; ?>>Privado</option>
                        <option value="publico" <?php echo ($dados['tipo'] === 'publico') ? 'selected' : ''; ?>>Público</option>
                    </select>
                    <?php if (isset($erros['tipo'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['tipo']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dias Fechado -->
            <div class="form-content">
                <label for="dias_fechado">Dias Fechado</label>
                <input type="text" id="dias_fechado" name="dias_fechado" placeholder="Ex: Domingos e Feriados"
                    value="<?php echo htmlspecialchars($dados['dias_fechado']); ?>"
                    class="<?php echo isset($erros['dias_fechado']) ? 'form-error' : ''; ?>" />
                <?php if (isset($erros['dias_fechado'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['dias_fechado']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Informações de Contato -->
            <h3>Informações de Contato</h3>

            <!-- Nome do Contato -->
            <div class="form-content">
                <label for="nome_contato">Nome do Contato</label>
                <input type="text" id="nome_contato" name="nome_contato" placeholder="Ex: João Silva"
                    value="<?php echo htmlspecialchars($dados['nome_contato']); ?>"
                    class="<?php echo isset($erros['nome_contato']) ? 'form-error' : ''; ?>" required />
                <?php if (isset($erros['nome_contato'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['nome_contato']); ?></p>
                <?php endif; ?>
            </div>

            <!-- E-mail e Telefone -->
            <div class="form-row">
                <div class="form-content">
                    <label for="email_contato">E-mail do Contato</label>
                    <input type="email" id="email_contato" name="email_contato" placeholder="Ex: contato@exemplo.com"
                        value="<?php echo htmlspecialchars($dados['email_contato']); ?>"
                        class="<?php echo isset($erros['email_contato']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['email_contato'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['email_contato']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-content">
                    <label for="telefone_contato">Telefone do Contato</label>
                    <input type="text" id="telefone_contato" name="telefone_contato" placeholder="Ex: (55) 99999-9999"
                        value="<?php echo htmlspecialchars($dados['telefone_contato']); ?>"
                        class="<?php echo isset($erros['telefone_contato']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['telefone_contato'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['telefone_contato']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Imagem -->
            <div class="form-content">
                <label for="imagem">Imagem do Local</label>
                <input type="file" id="imagem" name="imagem" accept="image/*" class="<?php echo isset($erros['imagem']) ? 'form-error' : ''; ?>" />
                <?php if (isset($erros['imagem'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['imagem']); ?></p>
                <?php endif; ?>
                <p class="info-message">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB.</p>
            </div>

            <!-- Botão de Envio -->
            <div class="form-content" style="text-align: center;">
                <div style="display: flex; justify-content: center; gap: 32px;">
                    <a href="index.php" class="btn-cadastro">Voltar ao Site</a>
                    <button type="submit" class="btn-cadastro">Cadastrar Local Turístico</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>
