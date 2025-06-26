<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'php/config.php';
require_once 'php/admin_functions.php';
require_once 'php/functions.php';

// Verificar se o usuário é administrador
if (!isAdmin($conexao, $_SESSION['usuario_id'])) {
    echo "<script>alert('Acesso negado. Apenas administradores podem acessar esta página.'); window.location.href = 'index.php';</script>";
    exit;
}

// Verificar se foi passado um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$evento_id = (int)$_GET['id'];
$evento = buscarEventoPorId($conexao, $evento_id);

if (!$evento) {
    echo "<script>alert('Evento não encontrado.'); window.location.href = 'admin.php';</script>";
    exit;
}

$nome_local = '';
if (!empty($evento['localEvento_idlocalEvento'])) {
    $nome_local = buscarNomeLocal($conexao, $evento['localEvento_idlocalEvento']);
}

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome_responsavel' => trim($_POST['nome_responsavel'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'nome_evento' => trim($_POST['nome_evento'] ?? ''),
        'data_inicial' => $_POST['data_inicial'] ?? '',
        'data_final' => $_POST['data_final'] ?? '',
        'horario' => $_POST['horario'] ?? '',
        'local' => trim($_POST['local'] ?? ''),
        'pago' => (int)($_POST['pago'] ?? 0),
        'valor' => $_POST['valor'] ?? 0,
        'categoria' => trim($_POST['categoria'] ?? ''),
        'info_descricao' => trim($_POST['info_descricao'] ?? '')
    ];
    
    // Validar dados
    $erros = validarFormulario($dados);
    
    if (empty($erros)) {
        $caminho_imagem_relativo = null;
        
        // Verificar se uma nova imagem foi enviada
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
            $resultado_upload = uploadImagem($_FILES['imagem'], __DIR__);
            
            if (isset($resultado_upload['erro'])) {
                $mensagem = $resultado_upload['erro'];
                $tipo_mensagem = 'error';
            } else {
                $caminho_imagem_relativo = $resultado_upload['caminho_relativo'];
                
                // Excluir imagem antiga se existir
                if ($evento['imagem']) {
                    $caminho_imagem_antiga = __DIR__ . '/' . $evento['imagem'];
                    if (file_exists($caminho_imagem_antiga)) {
                        unlink($caminho_imagem_antiga);
                    }
                }
            }
        }
        
        if ($tipo_mensagem !== 'error') {
            if (atualizarEventoAdmin($conexao, $evento_id, $dados, $caminho_imagem_relativo)) {
                $mensagem = 'Evento atualizado com sucesso!';
                $tipo_mensagem = 'success';
                
                // Recarregar dados do evento
                $evento = buscarEventoPorId($conexao, $evento_id);
            } else {
                $mensagem = 'Erro ao atualizar evento.';
                $tipo_mensagem = 'error';
            }
        }
    } else {
        $mensagem = 'Por favor, corrija os erros no formulário: ' . implode(', ', $erros);
        $tipo_mensagem = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento - Painel Administrativo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: #2c5530;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c5530;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .current-image {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        #valor-group {
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Editar Evento</h1>
            <p><?php echo htmlspecialchars($evento['nome_evento']); ?></p>
        </div>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem === 'success' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome_responsavel">Nome do Responsável</label>
                        <input type="text" id="nome_responsavel" name="nome_responsavel" value="<?php echo htmlspecialchars($evento['nome_responsavel']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($evento['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nome_evento">Nome do Evento</label>
                    <input type="text" id="nome_evento" name="nome_evento" value="<?php echo htmlspecialchars($evento['nome_evento']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_inicial">Data Inicial</label>
                        <input type="date" id="data_inicial" name="data_inicial" value="<?php echo $evento['data_inicial']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="data_final">Data Final</label>
                        <input type="date" id="data_final" name="data_final" value="<?php echo $evento['data_final']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="horario">Horário</label>
                        <input type="time" id="horario" name="horario" value="<?php echo $evento['horario']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="local">Local</label>
                    <input type="text" id="local" name="local" value="<?php echo htmlspecialchars($nome_local); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione uma categoria</option>
                            <option value="Cultura" <?php echo $evento['categoria'] === 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
                            <option value="Esporte" <?php echo $evento['categoria'] === 'Esporte' ? 'selected' : ''; ?>>Esporte</option>
                            <option value="Lazer" <?php echo $evento['categoria'] === 'Lazer' ? 'selected' : ''; ?>>Lazer</option>
                            <option value="Educação" <?php echo $evento['categoria'] === 'Educação' ? 'selected' : ''; ?>>Educação</option>
                            <option value="Negócios" <?php echo $evento['categoria'] === 'Negócios' ? 'selected' : ''; ?>>Negócios</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pago">Tipo de Evento</label>
                        <select id="pago" name="pago" required onchange="toggleValor()">
                            <option value="0" <?php echo $evento['pago'] == 0 ? 'selected' : ''; ?>>Gratuito</option>
                            <option value="1" <?php echo $evento['pago'] == 1 ? 'selected' : ''; ?>>Pago</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" id="valor-group" <?php echo $evento['pago'] == 1 ? 'style="display: block;"' : ''; ?>>
                    <label for="valor">Valor (R$)</label>
                    <input type="number" id="valor" name="valor" step="0.01" min="0" value="<?php echo $evento['valor']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="info_descricao">Descrição/Informações Adicionais</label>
                    <textarea id="info_descricao" name="info_descricao" required><?php echo htmlspecialchars($evento['info_descricao']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagem">Imagem do Evento</label>
                    <input type="file" id="imagem" name="imagem" accept="image/*">
                    <?php if ($evento['imagem']): ?>
                        <p>Imagem atual:</p>
                        <img src="<?php echo htmlspecialchars($evento['imagem']); ?>" alt="Imagem atual" class="current-image">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="admin.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleValor() {
            const pago = document.getElementById('pago').value;
            const valorGroup = document.getElementById('valor-group');
            
            if (pago === '1') {
                valorGroup.style.display = 'block';
                document.getElementById('valor').required = true;
            } else {
                valorGroup.style.display = 'none';
                document.getElementById('valor').required = false;
                document.getElementById('valor').value = '';
            }
        }
        
        // Inicializar o estado do campo valor
        toggleValor();
    </script>
</body>
</html>

