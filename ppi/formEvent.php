<?php

// Inicia a sessão para verificação de login
session_start();

$usuario_id = $_SESSION['usuario_id']?? null;  // pega o ID da sessão

// Inclui a configuração do banco PRIMEIRO para ter a variável $conexao
require_once(__DIR__ . '/php/config.php');
// Inclui as funções
require_once(__DIR__ . '/php/functions.php');
// Inclui as funções de local
require_once(__DIR__ . '/php/functions_local.php');

// Verifica se a conexão com o banco foi estabelecida em config.php
if (!$conexao || $conexao->connect_error) {
    error_log("Erro fatal em formEvent.php: Falha ao conectar ao banco de dados. Detalhes: " . ($conexao ? $conexao->connect_error : 
        'Variável de conexão não encontrada
    '));
    die("Erro crítico: Não foi possível conectar ao banco de dados. Por favor, tente mais tarde ou contate o administrador.");
}

$erros = [];
$mensagem_sucesso = '';
$mostrar_form_local = false;
$dados_local = [
    'nome_local' => '',
    'logradouro' => '',
    'numero' => '',
    'telefone' => '',
    'nome_contato' => '',
    'email_contato' => ''
];

// Inicializa $dados com todos os campos esperados pelo formulário e pelo backend
$dados = [
    'nome_responsavel' => '',
    'email' => '',
    'nome_evento' => '',
    'data_inicial' => '',
    'data_final' => '',
    'horario' => '',
    'local' => '',
    'pago_radio' => '', // Para manter o estado do radio ('sim'/'nao')
    'valor_input' => '', // Para manter o valor do campo de texto do preço
    'categoria_select' => '', // Para manter a categoria selecionada
    'info_descricao' => '', // Campo unificado para descrição/informações
    // Campos que serão derivados para o backend:
    'pago' => 0, // Valor numérico (0 ou 1)
    'valor' => '0.00', // Valor decimal formatado
    'categoria' => '', // Categoria única
];

// Verifica se o formulário de local foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_local'])) {
    // Coleta dados do formulário de local
    $dados_local = [
        'nome_local' => $_POST['nome_local'] ?? '',
        'logradouro' => $_POST['logradouro'] ?? '',
        'numero' => $_POST['numero'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'nome_contato' => $_POST['nome_contato'] ?? '',
        'email_contato' => $_POST['email_contato'] ?? ''
    ];

    // Validação básica
    $erros_local = [];
    if (empty($dados_local['nome_local'])) $erros_local['nome_local'] = "Nome do local é obrigatório";
    if (empty($dados_local['logradouro'])) $erros_local['logradouro'] = "Endereço é obrigatório";
    if (empty($dados_local['numero'])) $erros_local['numero'] = "Número é obrigatório";
    if (empty($dados_local['nome_contato'])) $erros_local['nome_contato'] = "Nome do contato é obrigatório";
    if (empty($dados_local['email_contato'])) $erros_local['email_contato'] = "E-mail do contato é obrigatório";
    if (empty($dados_local['telefone'])) $erros_local['telefone'] = "Telefone é obrigatório";

    if (empty($erros_local)) {
        // Adicionar contato
        $id_contato = adicionarContato(
            $conexao, 
            $dados_local['nome_contato'], 
            $dados_local['email_contato'], 
            $dados_local['telefone']
        );

        if ($id_contato) {
            // Adicionar local
            $id_local = adicionarLocal(
                $conexao, 
                $dados_local['nome_local'], 
                $dados_local['logradouro'], 
                $dados_local['numero'], 
                $id_contato
            );

            if ($id_local) {
                $mensagem_sucesso = "Local adicionado com sucesso!";
                // Preenche o campo local com o ID do local adicionado
                $dados['local'] = $id_local;
                $mostrar_form_local = false;
            } else {
                $erros['geral'] = "Erro ao adicionar local. Por favor, tente novamente.";
                $mostrar_form_local = true;
            }
        } else {
            $erros['geral'] = "Erro ao adicionar contato. Por favor, tente novamente.";
            $mostrar_form_local = true;
        }
    } else {
        $erros = array_merge($erros, $erros_local);
        $mostrar_form_local = true;
    }
}
// Verifica se o formulário de evento foi enviado
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Coleta e Preparação dos Dados --- 
    $dados_form = [
        'nome_responsavel' => $_POST['nome_responsavel'] ?? '',
        'email' => $_POST['email'] ?? '',
        'nome_evento' => $_POST['nome_evento'] ?? '',
        'data_inicial' => $_POST['data_inicial'] ?? '',
        'data_final' => $_POST['data_final'] ?? '',
        'horario' => $_POST['horario'] ?? '',
        'local' => $_POST['local'] ?? '',
        'pago_radio' => $_POST['pago'] ?? '', // 'sim' ou 'nao'
        'valor_input' => $_POST['valor'] ?? '', // Valor em texto do input
        'categoria_select' => $_POST['categoria'] ?? '', // Categoria do select
        'info_descricao' => $_POST['info_descricao'] ?? '', // Descrição/Informações do textarea
    ];

    // Atualiza $dados para repopular o formulário em caso de erro
    $dados = array_merge($dados, $dados_form);

    // Prepara os dados para validação e inserção no banco
    $dados_para_validar = $dados_form; // Copia os dados do form
    $dados_para_validar['pago'] = ($dados_form['pago_radio'] === 'sim') ? 1 : 0;
    $dados_para_validar['valor'] = ($dados_para_validar['pago'] === 1) ? trim($dados_form['valor_input']) : '0.00';
    // Remove caracteres não numéricos do valor, exceto ponto e vírgula, depois substitui vírgula por ponto.
    if ($dados_para_validar['pago'] === 1) {
        $valor_limpo = preg_replace('/[^0-9,.]/', '', $dados_para_validar['valor']);
        $valor_limpo = str_replace(',', '.', $valor_limpo);
        if (is_numeric($valor_limpo)) {
            $dados_para_validar['valor'] = number_format((float)$valor_limpo, 2, '.', ''); // Formata para 2 casas decimais
        } else {
            $dados_para_validar['valor'] = ''; // Invalido se não for numérico após limpeza
        }
    }
    $dados_para_validar['categoria'] = trim($dados_form['categoria_select']);
    // 'info_descricao' já está correto

    // Remove campos auxiliares que não vão para as funções
    unset($dados_para_validar['pago_radio']);
    unset($dados_para_validar['valor_input']);
    unset($dados_para_validar['categoria_select']);

    // --- Validação --- 
    $erros = validarFormulario($dados_para_validar);

    // Verifica se foi enviada uma imagem e se não houve erro no upload inicial
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] === UPLOAD_ERR_NO_FILE) {
        $erros['imagem'] = "A imagem é obrigatória";
    } elseif ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        // Erros como tamanho excedido (UPLOAD_ERR_INI_SIZE) são pegos aqui
        // A função uploadImagem dará uma mensagem mais específica se chegar lá
        $erros['imagem'] = "Erro no upload da imagem. Verifique o arquivo e tente novamente.";
    }

    // --- Verificação do Local ---
    $local_id = null;
    if (empty($erros['local'])) {
        $local_id = verificarLocalExiste($conexao, $dados_para_validar['local']);
        if (!$local_id) {
            // Local não existe, mostrar formulário para adicionar
            $mostrar_form_local = true;
            $dados_local['nome_local'] = $dados_para_validar['local']; // Pré-preenche o nome do local
            // Não interrompe o processamento, apenas marca a flag para mostrar o formulário
        }
    }

    // --- Processamento (se não houver erros e não estiver mostrando formulário de local) --- 
    if (empty($erros) && !$mostrar_form_local) {
        $base_path = __DIR__; // Diretório atual (onde está formEvent.php)
        $upload = uploadImagem($_FILES['imagem'], $base_path);

        if (isset($upload['erro'])) {
            $erros['imagem'] = $upload['erro'];
        } else {
            // Upload OK, tenta cadastrar no banco (passando o usuario_id)
            // Adiciona o ID do local ao array de dados
            $dados_para_validar['localEvento_idlocalEvento'] = $local_id;
            $dados_para_validar['id'] = $local_id;
            
            $resultado = cadastrarEvento($conexao, $dados_para_validar, $upload["caminho_relativo"], $usuario_id);

            if ($resultado) {
                $conexao->close();
                header("Location: eventos.php?sucesso=1");
                exit;
            } else {
                $erros['geral'] = "Erro ao cadastrar o evento no banco de dados. Verifique os dados e tente novamente. Detalhe técnico: " . $conexao->error;
                // Considerar excluir a imagem que foi salva se o cadastro falhar
                $caminho_abs_img = $base_path . '/' . $upload['caminho_relativo'];
                if (file_exists($caminho_abs_img)) {
                    unlink($caminho_abs_img);
                }
            }
        }
    }
    // Se $erros não estiver vazio, o formulário será reexibido com os erros.
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
    <title>Cadastrar Evento</title>
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
        /* Estilo para o modal de adicionar local */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .modal-header h2 {
            margin: 0;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="img">
        <img src="./img/escudo2.png" alt="Logo">
    </div>

    <div class="container">
        <section class="header">
            <h1>Cadastrar Evento</h1>
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

        <?php if ($mostrar_form_local): ?>
        <!-- Formulário para adicionar novo local -->
        <div class="modal-overlay" id="modalLocal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Adicionar Novo Local</h2>
                </div>
                
                <form method="POST" action="" class="form">
                    <input type="hidden" name="adicionar_local" value="1">
                    
                    <div class="form-content">
                        <label for="nome_local">Nome do Local</label>
                        <input type="text" id="nome_local" name="nome_local" 
                            value="<?php echo htmlspecialchars($dados_local['nome_local']); ?>"
                            class="<?php echo isset($erros['nome_local']) ? 'form-error' : ''; ?>" required />
                        <?php if (isset($erros['nome_local'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['nome_local']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-content">
                        <label for="logradouro">Endereço (Logradouro)</label>
                        <input type="text" id="logradouro" name="logradouro" 
                            value="<?php echo htmlspecialchars($dados_local['logradouro']); ?>"
                            class="<?php echo isset($erros['logradouro']) ? 'form-error' : ''; ?>" required />
                        <?php if (isset($erros['logradouro'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['logradouro']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-content">
                        <label for="numero">Número</label>
                        <input type="text" id="numero" name="numero" 
                            value="<?php echo htmlspecialchars($dados_local['numero']); ?>"
                            class="<?php echo isset($erros['numero']) ? 'form-error' : ''; ?>" required />
                        <?php if (isset($erros['numero'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['numero']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-content">
                        <label for="telefone">Telefone do Local</label>
                        <input type="text" id="telefone" name="telefone" 
                            value="<?php echo htmlspecialchars($dados_local['telefone']); ?>"
                            class="<?php echo isset($erros['telefone']) ? 'form-error' : ''; ?>" required />
                        <?php if (isset($erros['telefone'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['telefone']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-content">
                        <label for="nome_contato">Nome do Contato</label>
                        <input type="text" id="nome_contato" name="nome_contato" 
                            value="<?php echo htmlspecialchars($dados_local['nome_contato']); ?>"
                            class="<?php echo isset($erros['nome_contato']) ? 'form-error' : ''; ?>" required />
                        <?php if (isset($erros['nome_contato'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['nome_contato']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-content">
                        <label for="email_contato">E-mail do Contato</label>
                        <input type="email" id="email_contato" name="email_contato" 
                            value="<?php echo htmlspecialchars($dados_local['email_contato']); ?>"
                            class="<?php echo isset($erros['email_contato']) ? 'form-error' : ''; ?>" required />
                        <?php if (isset($erros['email_contato'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['email_contato']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-content" style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn">Adicionar Local</button>
                        <a href="formEvent.php" class="btn" style="background-color: #6c757d; text-decoration: none; display: inline-block; margin-left: 10px;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Formulário de cadastro de evento -->
        <form id="form" class="form" method="POST" enctype="multipart/form-data" novalidate>
            <!-- Nome Responsável e E-mail -->
            <div class="form-row">
                <div class="form-content">
                    <label for="nome_responsavel">Nome do Responsável</label>
                    <input type="text" id="nome_responsavel" name="nome_responsavel" placeholder="Nome Completo"
                        value="<?php echo htmlspecialchars($dados['nome_responsavel']); ?>"
                        class="<?php echo isset($erros['nome_responsavel']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['nome_responsavel'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['nome_responsavel']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-content">
                    <label for="email">E-mail de Contato</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com"
                        value="<?php echo htmlspecialchars($dados['email']); ?>"
                        class="<?php echo isset($erros['email']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['email'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['email']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nome do Evento -->
            <div class="form-content">
                <label for="nome_evento">Nome do Evento</label>
                <input type="text" id="nome_evento" name="nome_evento" placeholder="Ex: Festa Junina da Comunidade"
                    value="<?php echo htmlspecialchars($dados['nome_evento']); ?>"
                    class="<?php echo isset($erros['nome_evento']) ? 'form-error' : ''; ?>" required />
                <?php if (isset($erros['nome_evento'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($erros['nome_evento']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Data Inicial e Data Final -->
            <div class="form-row">
                <div class="form-content">
                    <label for="data_inicial">Data Inicial</label>
                    <input type="date" id="data_inicial" name="data_inicial"
                        value="<?php echo htmlspecialchars($dados['data_inicial']); ?>"
                        class="<?php echo isset($erros['data_inicial']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['data_inicial'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['data_inicial']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-content">
                    <label for="data_final">Data Final</label>
                    <input type="date" id="data_final" name="data_final"
                        value="<?php echo htmlspecialchars($dados['data_final']); ?>"
                        class="<?php echo isset($erros['data_final']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['data_final'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['data_final']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Horário e Local -->
            <div class="form-row">
                <div class="form-content">
                    <label for="horario">Horário</label>
                    <input type="time" id="horario" name="horario"
                        value="<?php echo htmlspecialchars($dados['horario']); ?>"
                        class="<?php echo isset($erros['horario']) ? 'form-error' : ''; ?>" required />
                    <?php if (isset($erros['horario'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['horario']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-content">
                    <label for="local">Local</label>
                    <input type="text" id="local" name="local"
                        value="<?php echo htmlspecialchars($dados['local']); ?>"
                        class="<?php echo isset($erros['local']) ? 'form-error' : ''; ?>" required
                        placeholder="Digite o nome do local do evento" />
                    <?php if (isset($erros['local'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['local']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagamento -->
            <div class="form-content">
                <label>O evento será pago?</label>
                <div>
                    <label>
                        <input type="radio" name="pago" value="sim" <?php echo ($dados['pago_radio'] ?? '') === 'sim' ? 'checked' : ''; ?> required> Sim
                    </label>
                    <label>
                        <input type="radio" name="pago" value="nao" <?php echo ($dados['pago_radio'] ?? '') === 'nao' ? 'checked' : ''; ?>> Não
                    </label>
                </div>
                 <?php if (isset($erros['pago'])): // Erro vindo da validação do backend ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['pago']); ?></p>
                <?php endif; ?>

                <div id="valorIngresso" style="<?php echo ($dados['pago_radio'] ?? '') === 'sim' ? 'display: block;' : 'display: none;'; ?> margin-top: 10px;">
                    <label for="valor">Informe o valor do ingresso:</label>
                    <input type="text" id="valor" name="valor" placeholder="Ex: 50.00 ou 50,00"
                        value="<?php echo htmlspecialchars($dados['valor_input']); ?>"
                        class="<?php echo isset($erros['valor']) ? 'form-error' : ''; ?>" />
                     <?php if (isset($erros['valor'])): ?>
                        <p class="error-message"><?php echo htmlspecialchars($erros['valor']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categoria -->
            <div class="form-content">
                <label for="categoria">Categoria do Evento</label>
                <select id="categoria" name="categoria" class="<?php echo isset($erros['categoria']) ? 'form-error' : ''; ?>" required>
                    <option value="" <?php echo empty($dados['categoria_select']) ? 'selected' : ''; ?>>-- Selecione --</option>
                    <option value="Lazer" <?php echo ($dados['categoria_select'] ?? '') === 'Lazer' ? 'selected' : ''; ?>>Lazer</option>
                    <option value="Turismo rural" <?php echo ($dados['categoria_select'] ?? '') === 'Turismo rural' ? 'selected' : ''; ?>>Turismo Rural</option>
                    <option value="Turismo de natureza" <?php echo ($dados['categoria_select'] ?? '') === 'Turismo de natureza' ? 'selected' : ''; ?>>Turismo de Natureza</option>
                    <option value="Religioso" <?php echo ($dados['categoria_select'] ?? '') === 'Religioso' ? 'selected' : ''; ?>>Religioso</option>
                    <option value="Evento esportivo" <?php echo ($dados['categoria_select'] ?? '') === 'Evento esportivo' ? 'selected' : ''; ?>>Evento Esportivo</option>
                    <option value="Eventos acadêmicos" <?php echo ($dados['categoria_select'] ?? '') === 'Eventos acadêmicos' ? 'selected' : ''; ?>>Eventos Acadêmicos</option>
                    <option value="Corporativos" <?php echo ($dados['categoria_select'] ?? '') === 'Corporativos' ? 'selected' : ''; ?>>Corporativos</option>
                    <option value="Outros" <?php echo ($dados['categoria_select'] ?? '') === 'Outros' ? 'selected' : ''; ?>>Outros</option>
                </select>
                 <?php if (isset($erros['categoria'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['categoria']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Descrição / Informações -->
            <div class="form-content">
                <label for="info_descricao">Descrição / Informações Adicionais</label>
                <textarea id="info_descricao" name="info_descricao" placeholder="Descreva detalhes sobre o evento..."
                    class="<?php echo isset($erros['info_descricao']) ? 'form-error' : ''; ?>" required><?php echo htmlspecialchars($dados['info_descricao']); ?></textarea>
                <?php if (isset($erros['info_descricao'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['info_descricao']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Imagem -->
            <div class="form-content">
                <label for="imagem">Imagem do Evento</label>
                <input type="file" id="imagem" name="imagem" accept="image/*" class="<?php echo isset($erros['imagem']) ? 'form-error' : ''; ?>" required />
                <?php if (isset($erros['imagem'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($erros['imagem']); ?></p>
                <?php endif; ?>
                <p class="info-message">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB.</p>
            </div>

            <!-- Botão de Envio -->
            <div class="form-content" style="text-align: center;">
                <div style="display: flex; justify-content: center; gap: 32px; margin-top: 24px;">
                    <a href="index.php" class="btn-cadastro">Voltar ao Site</a>
                    <button type="submit" class="btn-cadastro">Cadastrar Evento</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Script para mostrar/esconder o campo de valor do ingresso
        document.addEventListener('DOMContentLoaded', function() {
            const radioSim = document.querySelector('input[name="pago"][value="sim"]');
            const radioNao = document.querySelector('input[name="pago"][value="nao"]');
            const valorIngresso = document.getElementById('valorIngresso');

            if (radioSim && radioNao && valorIngresso) {
                radioSim.addEventListener('change', function() {
                    valorIngresso.style.display = 'block';
                });

                radioNao.addEventListener('change', function() {
                    valorIngresso.style.display = 'none';
                });
            }
        });
    </script>
</body>

</html>
