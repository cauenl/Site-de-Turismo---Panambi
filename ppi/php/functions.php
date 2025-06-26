<?php
// Não incluir config.php aqui. A conexão será passada como parâmetro.

define('UPLOAD_DIR_REL', 'uploads/eventos/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Função para validar dados do formulário
function validarFormulario($dados)
{
    $erros = [];

    if (empty($dados['nome_responsavel'])) {
        $erros['nome_responsavel'] = "Nome do responsável é obrigatório";
    }

    if (empty($dados['email'])) {
        $erros['email'] = "E-mail é obrigatório";
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = "E-mail inválido";
    }

    if (empty($dados['nome_evento'])) {
        $erros['nome_evento'] = "Nome do evento é obrigatório";
    }

    if (empty($dados['data_inicial'])) {
        $erros['data_inicial'] = "Data inicial é obrigatória";
    }

    if (empty($dados['data_final'])) {
        $erros['data_final'] = "Data final é obrigatória";
    } elseif (!empty($dados['data_inicial']) && $dados['data_final'] < $dados['data_inicial']) {
        $erros['data_final'] = "Data final deve ser maior ou igual à data inicial";
    }

    if (empty($dados['horario'])) {
        $erros['horario'] = "Horário é obrigatório";
    }

    if (empty($dados['local'])) {
        $erros['local'] = "Local é obrigatório";
    }

    if (!isset($dados['pago']) || !in_array($dados['pago'], [0, 1], true)) {
        $erros['pago'] = "Informação se é pago ou gratuito é obrigatória";
    }

    if (isset($dados['pago']) && $dados['pago'] === 1) {
        if (empty($dados['valor']) || !is_numeric($dados['valor']) || (float) $dados['valor'] <= 0) {
            $erros['valor'] = "Informe um valor numérico válido e maior que zero para o evento pago";
        }
    }

    if (empty($dados['categoria'])) {
        $erros['categoria'] = "Categoria é obrigatória";
    }

    if (empty($dados['info_descricao'])) {
        $erros['info_descricao'] = "Descrição/Informações adicionais são obrigatórias";
    }

    return $erros;
}

// Função para upload de imagem
function uploadImagem($arquivo, $base_path)
{
    $upload_path_abs = rtrim($base_path, '/') . '/' . UPLOAD_DIR_REL;

    if (!file_exists($upload_path_abs)) {
        if (!mkdir($upload_path_abs, 0755, true)) {
            return ['erro' => 'Falha ao criar o diretório de uploads: ' . $upload_path_abs];
        }
    }

    if (!is_writable($upload_path_abs)) {
        return ['erro' => 'O diretório de uploads (' . $upload_path_abs . ') não tem permissão de escrita.'];
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        switch ($arquivo['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['erro' => 'Arquivo muito grande. Verifique o tamanho máximo permitido.'];
            case UPLOAD_ERR_PARTIAL:
                return ['erro' => 'O upload do arquivo foi feito parcialmente.'];
            case UPLOAD_ERR_NO_FILE:
                return ['erro' => 'Nenhum arquivo foi enviado.'];
            case UPLOAD_ERR_NO_TMP_DIR:
                return ['erro' => 'Diretório temporário ausente no servidor.'];
            case UPLOAD_ERR_CANT_WRITE:
                return ['erro' => 'Falha ao escrever o arquivo no disco no servidor.'];
            case UPLOAD_ERR_EXTENSION:
                return ['erro' => 'Uma extensão do PHP impediu o upload do arquivo.'];
            default:
                return ['erro' => 'Erro desconhecido no upload da imagem. Código: ' . $arquivo['error']];
        }
    }

    if ($arquivo['size'] > MAX_UPLOAD_SIZE) {
        return ['erro' => 'Arquivo muito grande. Tamanho máximo: ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipo_arquivo_real = $finfo->file($arquivo['tmp_name']);

    if (!in_array($tipo_arquivo_real, ALLOWED_TYPES)) {
        return ['erro' => 'Tipo de arquivo não permitido (' . htmlspecialchars($tipo_arquivo_real) . '). Apenas JPG, PNG e GIF são aceitos'];
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $nome_arquivo = sha1_file($arquivo['tmp_name']) . '_' . time() . '.' . $extensao;
    $caminho_destino_abs = $upload_path_abs . $nome_arquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_destino_abs)) {
        return ['erro' => 'Falha ao mover o arquivo para o diretório de uploads'];
    }

    $caminho_relativo = UPLOAD_DIR_REL . $nome_arquivo;
    return ['sucesso' => true, 'caminho_relativo' => $caminho_relativo];
}

// Função para cadastrar evento (agora com aprovação pendente por padrão)
function cadastrarEvento($conexao, $dados, $caminho_imagem_relativo, $usuario_id)
{
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em cadastrarEvento: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    // Adiciona usuario_id ao SQL INSERT e define aprovado = 0 (pendente)
    $sql = "INSERT INTO eventos (
                nome_responsavel, 
                email, 
                nome_evento, 
                data_inicial, 
                data_final, 
                horario, 
                localEvento_idlocalEvento,
                pago, 
                valor, 
                categoria, 
                info_descricao, 
                imagem,
                usuario_id,
                aprovado 
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Adicionado um placeholder

    $stmt = $conexao->prepare($sql);
    if ($stmt === false) {
        error_log("Erro ao preparar statement em cadastrarEvento: " . $conexao->error . " SQL: " . $sql);
        return false;
    }


    $aprovado = 0; // Pendente por padrão
    var_dump($dados['local']);
    // Adiciona 'ii' para integer e as variáveis $usuario_id e $aprovado ao bind_param
    $stmt->bind_param(
        "ssssssiidsssii", // Adicionado 'i' no final para aprovado
        $dados['nome_responsavel'],
        $dados['email'],
        $dados['nome_evento'],
        $dados['data_inicial'],
        $dados['data_final'],
        $dados['horario'],
        $dados['localEvento_idlocalEvento'],
        $dados['pago'],
        $dados['valor'],
        $dados['categoria'],
        $dados['info_descricao'],
        $caminho_imagem_relativo,
        $usuario_id,
        $aprovado // Adicionada a variável
    );

    $resultado = $stmt->execute();
    if ($resultado === false) {
        error_log("Erro ao executar statement em cadastrarEvento: " . $stmt->error);
    }

    $stmt->close();
    return $resultado;
}

// Função para listar todos os eventos aprovados (para exibição pública)
function listarEventos($conexao)
{
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarEventos: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }

    $sql = "SELECT e.*, l.nome AS local
            FROM eventos e
            LEFT JOIN localevento l ON e.localEvento_idlocalEvento = l.idlocalEvento
            WHERE e.aprovado = 1
            ORDER BY e.data_inicial ASC";
    $resultado = $conexao->query($sql);

    $eventos = [];

    if ($resultado === false) {
        error_log("Erro ao executar query em listarEventos: " . $conexao->error);
        return [];
    } elseif ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $eventos[] = $row;
        }
    }

    return $eventos;
}

// Função para excluir um evento
function excluirEvento($conexao, $id, $base_path, $usuario_id = null)
{
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em excluirEvento: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    $caminho_imagem_relativo = null;
    $evento_usuario_id = null;
    $idlocalEvento = null;

    // Busca informações do evento incluindo o usuario_id
    $sql_select = "SELECT imagem, usuario_id, localEvento_idlocalEvento FROM eventos WHERE id = ?";
    $stmt_select = $conexao->prepare($sql_select);
    if ($stmt_select) {
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $stmt_select->bind_result($caminho_imagem_relativo, $evento_usuario_id, $idlocalEvento);
        $stmt_select->fetch();
        $stmt_select->close();
    } else {
        error_log("Erro ao preparar select em excluirEvento: " . $conexao->error);
        return false;
    }

    // Se não encontrou o evento
    if ($evento_usuario_id === null) {
        error_log("Evento não encontrado para exclusão: ID " . $id);
        return false;
    }

    // Verifica se o usuário tem permissão para excluir (se usuario_id foi fornecido)
    if ($usuario_id !== null && $evento_usuario_id != $usuario_id) {
        error_log("Usuário " . $usuario_id . " tentou excluir evento " . $id . " que não lhe pertence");
        return false;
    }

    // Deleta o evento
    $sql_delete = "DELETE FROM eventos WHERE id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    if (!$stmt_delete) {
        error_log("Erro ao preparar delete em excluirEvento: " . $conexao->error);
        return false;
    }

    $stmt_delete->bind_param("i", $id);
    $resultado_delete = $stmt_delete->execute();
    if ($resultado_delete === false) {
        error_log("Erro ao executar delete em excluirEvento: " . $stmt_delete->error);
    }
    $stmt_delete->close();

    // Deleta o local (se for exclusivo)
    if ($idlocalEvento) {
        // Verifica se mais algum evento usa esse local
        $sql_check = "SELECT COUNT(*) FROM eventos WHERE localEvento_idlocalEvento = ?";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->bind_param("i", $idlocalEvento);
        $stmt_check->execute();
        $count = 0;
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count == 0) {
            // Ninguém mais usa, pode deletar
            $sql_del_local = "DELETE FROM localevento WHERE idlocalEvento = ?";
            $stmt_del_local = $conexao->prepare($sql_del_local);
            $stmt_del_local->bind_param("i", $idlocalEvento);
            $stmt_del_local->execute();
            $stmt_del_local->close();
        }
    }

    // Remove a imagem se a exclusão foi bem-sucedida
    if ($resultado_delete && $caminho_imagem_relativo) {
        $caminho_imagem_abs = rtrim($base_path, '/') . '/' . $caminho_imagem_relativo;
        if (file_exists($caminho_imagem_abs)) {
            if (!unlink($caminho_imagem_abs)) {
                error_log("Erro ao excluir arquivo de imagem: " . $caminho_imagem_abs);
            }
        } else {
            error_log("Arquivo de imagem não encontrado para exclusão: " . $caminho_imagem_abs);
        }
    }

    return $resultado_delete;
}

// Função para formatar data
function formatarData($data)
{
    if (empty($data) || $data === '0000-00-00') {
        return '';
    }
    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return '';
    }
    return date("d/m/Y", $timestamp);
}

// Função para formatar hora
function formatarHora($hora)
{
    if (empty($hora)) {
        return '';
    }
    $timestamp = strtotime($hora);
    if ($timestamp === false) {
        return '';
    }
    return date("H:i", $timestamp);
}

function buscarNomeLocal($conexao, $idlocalEvento) {
    $sql = "SELECT nome FROM localevento WHERE idlocalEvento = ?";
    $stmt = $conexao->prepare($sql);
    $nome = null;
    $stmt->bind_param("i", $idlocalEvento);
    $stmt->execute();
    $stmt->bind_result($nome);
    $stmt->fetch();
    $stmt->close();
    return $nome ?: '';
}
?>