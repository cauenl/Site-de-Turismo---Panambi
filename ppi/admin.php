<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'php/config.php';
require_once 'php/admin_functions.php';

// Verificar se o usuário é administrador
if (!isAdmin($conexao, $_SESSION['usuario_id'])) {
    // Redirecionamento seguro, sem JavaScript
    header('Location: index.php?erro=acesso_admin');
    exit;
}

// Mensagem de feedback após ações
$mensagem = $_GET['msg'] ?? '';
$tipo_mensagem = $_GET['tipo'] ?? '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $mensagem = '';
    $tipo_mensagem = '';

    switch ($acao) {
        case 'aprovar_evento':
            $evento_id = (int)$_POST['evento_id'];
            if (aprovarEvento($conexao, $evento_id, $_SESSION['usuario_id'])) {
                $mensagem = 'Evento aprovado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao aprovar evento.';
                $tipo_mensagem = 'error';
            }
            break;
        case 'rejeitar_evento':
            $evento_id = (int)$_POST['evento_id'];
            if (rejeitarEvento($conexao, $evento_id, $_SESSION['usuario_id'])) {
                $mensagem = 'Evento rejeitado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao rejeitar evento.';
                $tipo_mensagem = 'error';
            }
            break;
        case 'excluir_evento':
            $evento_id = (int)$_POST['evento_id'];
            if (excluirEventoAdmin($conexao, $evento_id, __DIR__)) {
                $mensagem = 'Evento excluído com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao excluir evento.';
                $tipo_mensagem = 'error';
            }
            break;
        case 'aprovar_local':
            $local_id = (int)$_POST['local_id'];
            if (aprovarLocalTuristico($conexao, $local_id, $_SESSION['usuario_id'])) {
                $mensagem = 'Local turístico aprovado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao aprovar local turístico.';
                $tipo_mensagem = 'error';
            }
            break;
        case 'reprovar_local':
            $local_id = (int)$_POST['local_id'];
            if (reprovarLocalTuristico($conexao, $local_id, $_SESSION['usuario_id'])) {
                $mensagem = 'Local turístico reprovado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao reprovar local turístico.';
                $tipo_mensagem = 'error';
            }
            break;
        case 'excluir_local':
            $local_id = (int)$_POST['local_id'];
            if (excluirLocalTuristicoAdmin($conexao, $local_id)) {
                $mensagem = 'Local turístico excluído com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao excluir local turístico.';
                $tipo_mensagem = 'error';
            }
            break;
    }
    // Redireciona para evitar repost e atualizar a lista
    header("Location: admin.php?msg=" . urlencode($mensagem) . "&tipo=" . urlencode($tipo_mensagem));
    exit;
}

// Buscar dados
$eventos_pendentes = listarEventosPendentes($conexao);
$eventos_aprovados = listarEventosAprovados($conexao);
$locais_pendentes = listarLocaisTuristicosPendentes($conexao);
$locais_aprovados = listarLocaisTuristicosAprovados($conexao);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Panambi Turismo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(44, 85, 48, 0.3);
        }
        
        .admin-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }
        
        .admin-header p {
            margin: 0 0 20px 0;
            opacity: 0.9;
        }
        
        .admin-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
        }
        
        .admin-section h2 {
            color: #2c5530;
            border-bottom: 3px solid #2c5530;
            padding-bottom: 15px;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        
        .item-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        .item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.1);
            border-color: #2c5530;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .item-title {
            font-weight: bold;
            color: #2c5530;
            margin: 0;
            font-size: 1.3em;
        }
        
        .item-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pendente {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-aprovado {
            background: linear-gradient(135deg, #d4edda, #00b894);
            color: white;
            border: 1px solid #00b894;
        }
        
        .item-details {
            margin: 15px 0;
            color: #555;
            line-height: 1.6;
        }
        
        .item-details p {
            margin: 8px 0;
        }
        
        .item-details strong {
            color: #2c5530;
        }
        
        .item-actions {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1aa085);
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #c0392b);
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #f39c12);
            color: #212529;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #e0a800, #d68910);
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 5px solid;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left-color: #28a745;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 25px;
            overflow-x: auto;
        }
        
        .tab {
            padding: 15px 25px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            font-weight: 500;
            color: #6c757d;
        }
        
        .tab:hover {
            background: #f8f9fa;
            color: #2c5530;
        }
        
        .tab.active {
            border-bottom-color: #2c5530;
            color: #2c5530;
            font-weight: bold;
            background: #f8f9fa;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .no-items {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 2px dashed #dee2e6;
        }
        
        .no-items::before {
            content: "📋";
            display: block;
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .admin-container {
                padding: 10px;
            }
            
            .admin-header {
                padding: 20px;
            }
            
            .admin-header h1 {
                font-size: 2em;
            }
            
            .admin-section {
                padding: 20px;
            }
            
            .item-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .item-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                text-align: center;
                border-bottom: none;
                border-left: 3px solid transparent;
            }
            
            .tab.active {
                border-left-color: #2c5530;
                border-bottom-color: transparent;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Painel Administrativo</h1>
            <p>Gerenciamento de Eventos e Locais Turísticos</p>
            <a href="index.php" class="btn btn-primary">Voltar ao Site</a>
            <a href="php/logout.php" class="btn btn-danger">Sair</a>
        </div>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem === 'success' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-section">
            <div class="tabs">
                <div class="tab active" onclick="showTab('eventos-pendentes')">Eventos Pendentes (<?php echo count($eventos_pendentes); ?>)</div>
                <div class="tab" onclick="showTab('eventos-aprovados')">Eventos Aprovados (<?php echo count($eventos_aprovados); ?>)</div>
                <div class="tab" onclick="showTab('locais-pendentes')">Locais Pendentes (<?php echo count($locais_pendentes); ?>)</div>
                <div class="tab" onclick="showTab('locais-aprovados')">Locais Cadastrados (<?php echo count($locais_aprovados); ?>)</div> <!-- NOVA ABA -->
            </div>
            
            <!-- Eventos Pendentes -->
            <div id="eventos-pendentes" class="tab-content active">
                <h2>Eventos Pendentes de Aprovação</h2>
                <?php if (empty($eventos_pendentes)): ?>
                    <div class="no-items">Nenhum evento pendente de aprovação.</div>
                <?php else: ?>
                    <?php foreach ($eventos_pendentes as $evento): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <h3 class="item-title"><?php echo htmlspecialchars($evento['nome_evento']); ?></h3>
                                <span class="item-status status-pendente">Pendente</span>
                            </div>
                            <div class="item-details">
                                <p><strong>Responsável:</strong> <?php echo htmlspecialchars($evento['nome_responsavel']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($evento['email']); ?></p>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($evento['data_inicial'])); ?> a <?php echo date('d/m/Y', strtotime($evento['data_final'])); ?></p>
                                <p><strong>Local:</strong> <?php echo htmlspecialchars($evento['nome_local']); ?></p>
                                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($evento['categoria']); ?></p>
                                <p><strong>Valor:</strong> <?php echo $evento['pago'] ? 'R$ ' . number_format($evento['valor'], 2, ',', '.') : 'Gratuito'; ?></p>
                                <p><strong>Descrição:</strong> <?php echo htmlspecialchars(substr($evento['info_descricao'], 0, 200)); ?>...</p>
                                <p><strong>Cadastrado em:</strong> <?php echo date('d/m/Y H:i', strtotime($evento['data_cadastro'])); ?></p>
                            </div>
                            <div class="item-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="aprovar_evento">
                                    <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Aprovar este evento?')">Aprovar</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="rejeitar_evento">
                                    <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Rejeitar este evento?')">Rejeitar</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="excluir_evento">
                                    <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Excluir permanentemente este evento?')">Excluir</button>
                                </form>
                                <a href="editar_evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-primary">Editar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Eventos Aprovados -->
            <div id="eventos-aprovados" class="tab-content">
                <h2>Eventos Aprovados</h2>
                <?php if (empty($eventos_aprovados)): ?>
                    <div class="no-items">Nenhum evento aprovado.</div>
                <?php else: ?>
                    <?php foreach ($eventos_aprovados as $evento): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <h3 class="item-title"><?php echo htmlspecialchars($evento['nome_evento']); ?></h3>
                                <span class="item-status status-aprovado">Aprovado</span>
                            </div>
                            <div class="item-details">
                                <p><strong>Responsável:</strong> <?php echo htmlspecialchars($evento['nome_responsavel']); ?></p>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($evento['data_inicial'])); ?> a <?php echo date('d/m/Y', strtotime($evento['data_final'])); ?></p>
                                <p><strong>Local:</strong> <?php echo htmlspecialchars($evento['local']); ?></p>
                                <p><strong>Aprovado por:</strong> <?php echo htmlspecialchars($evento['aprovado_por_nome'] ?? 'N/A'); ?></p>
                                <p><strong>Data de aprovação:</strong> <?php echo $evento['data_aprovacao'] ? date('d/m/Y H:i', strtotime($evento['data_aprovacao'])) : 'N/A'; ?></p>
                            </div>
                            <div class="item-actions">
                                <a href="editar_evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-primary">Editar</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="excluir_evento">
                                    <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Excluir permanentemente este evento?')">Excluir</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Locais Turísticos Pendentes -->
            <div id="locais-pendentes" class="tab-content">
                <h2>Locais Turísticos Pendentes</h2>
                <?php if (empty($locais_pendentes)): ?>
                    <div class="no-items">Nenhum local turístico pendente de aprovação.</div>
                <?php else: ?>
                    <?php foreach ($locais_pendentes as $local): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <h3 class="item-title"><?php echo htmlspecialchars($local['descricao']); ?></h3>
                                <span class="item-status status-pendente">Pendente</span>
                            </div>
                            <div class="item-details">
                                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($local['endereco']); ?></p>
                                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($local['categoria']); ?></p>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($local['tipo']); ?></p>
                                <p><strong>Dias fechado:</strong> <?php echo htmlspecialchars($local['dias_fechado'] ?? 'N/A'); ?></p>
                                <p><strong>Cadastrado por:</strong> <?php echo htmlspecialchars($local['nome_usuario'] ?? 'N/A'); ?></p>
                                <p><strong>Cadastrado em:</strong> <?php echo date('d/m/Y H:i', strtotime($local['data_cadastro'])); ?></p>
                            </div>
                            <div class="item-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="aprovar_local">
                                    <input type="hidden" name="local_id" value="<?php echo $local['idLocal']; ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Aprovar este local turístico?')">Aprovar</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="reprovar_local">
                                    <input type="hidden" name="local_id" value="<?php echo $local['idLocal']; ?>">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Reprovar este local turístico?')">Reprovar</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="excluir_local">
                                    <input type="hidden" name="local_id" value="<?php echo $local['idLocal']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Excluir permanentemente este local turístico?')">Excluir</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Locais Turísticos Aprovados -->
            <div id="locais-aprovados" class="tab-content">
                <h2>Locais Turísticos Cadastrados</h2>
                <?php if (empty($locais_aprovados)): ?>
                    <div class="no-items">Nenhum local turístico cadastrado.</div>
                <?php else: ?>
                    <?php foreach ($locais_aprovados as $local): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <h3 class="item-title"><?php echo htmlspecialchars($local['descricao']); ?></h3>
                                <span class="item-status status-aprovado">Aprovado</span>
                            </div>
                            <div class="item-details">
                                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($local['endereco']); ?></p>
                                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($local['categoria']); ?></p>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($local['tipo']); ?></p>
                                <p><strong>Dias fechado:</strong> <?php echo htmlspecialchars($local['dias_fechado'] ?? 'N/A'); ?></p>
                                <p><strong>Cadastrado por:</strong> <?php echo htmlspecialchars($local['nome_usuario'] ?? 'N/A'); ?></p>
                                <p><strong>Aprovado por:</strong> <?php echo htmlspecialchars($local['aprovado_por_nome'] ?? 'N/A'); ?></p>
                                <p><strong>Data de aprovação:</strong> <?php echo $local['data_aprovacao'] ? date('d/m/Y H:i', strtotime($local['data_aprovacao'])) : 'N/A'; ?></p>
                            </div>
                            <div class="item-actions">
                                <!-- <a href="editar_local.php?id=<?php echo $local['idLocal']; ?>" class="btn btn-primary">Editar</a> -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="excluir_local">
                                    <input type="hidden" name="local_id" value="<?php echo $local['idLocal']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Excluir permanentemente este local turístico?')">Excluir</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabId) {
            // Esconder todas as abas
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remover classe active de todas as tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Mostrar a aba selecionada
            document.getElementById(tabId).classList.add('active');
            
            // Adicionar classe active na tab clicada
            event.target.classList.add('active');
        }
    </script>
</body>
</html>