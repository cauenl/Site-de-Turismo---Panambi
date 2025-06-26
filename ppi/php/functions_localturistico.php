<?php
// Funções relacionadas ao gerenciamento de locais turísticos

// Função para adicionar um novo local turístico
function adicionarLocalTuristico($conexao, $dados, $usuario_id, $contato_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em adicionarLocalTuristico: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    $sql = "INSERT INTO localturismo (nome, descricao, endereco, categoria, tipo, dias_fechado, imagem, usuario_id, contato_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    
    if ($stmt === false) {
        error_log("Erro ao preparar statement em adicionarLocalTuristico: " . $conexao->error);
        return false;
    }

    $stmt->bind_param("sssssssii", 
        $dados['nome'], 
        $dados['descricao'], 
        $dados['endereco'], 
        $dados['categoria'], 
        $dados['tipo'], 
        $dados['dias_fechado'], 
        $dados['imagem'], // novo campo
        $usuario_id, 
        $contato_id
    );
    
    if ($stmt->execute()) {
        $id_local = $stmt->insert_id;
        $stmt->close();
        return $id_local;
    } else {
        error_log("Erro ao executar statement em adicionarLocalTuristico: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

// Função para listar locais turísticos por categoria
function listarLocaisTuristicosPorCategoria($conexao, $categoria) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarLocaisTuristicosPorCategoria: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }

    $sql = "SELECT l.*, c.nome as contato_nome, c.email, c.telefone 
            FROM localturismo l 
            JOIN contato c ON l.contato_id = c.idContato
            WHERE l.categoria = ? AND l.aprovado = 1
            ORDER BY l.nome ASC";
    
    $stmt = $conexao->prepare($sql);
    if ($stmt === false) {
        error_log("Erro ao preparar statement em listarLocaisTuristicosPorCategoria: " . $conexao->error);
        return [];
    }

    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $locais = [];
    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $locais[] = $row;
        }
    }
    
    $stmt->close();
    return $locais;
}

// Função para obter o nome da página PHP correspondente à categoria
function obterPaginaCategoria($categoria) {
    switch (strtolower($categoria)) {
        case 'gastronomia':
            return 'gastronomia.php';
        case 'hospedagem':
            return 'hospedagem.php';
        case 'ponto turístico':
            return 'turismo.php';
        case 'local rural':
            return 'TurismoRural.php';
        default:
            return 'index.php';
    }
}

// Função para excluir um local turístico
function excluirLocalTuristico($conexao, $id, $usuario_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em excluirLocalTuristico: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    // Primeiro, verifica se o local pertence ao usuário logado
    $sql_verificar = "SELECT usuario_id, contato_id FROM localturismo WHERE idLocal = ?";
    $stmt_verificar = $conexao->prepare($sql_verificar);
    if (!$stmt_verificar) {
        error_log("Erro ao preparar verificação em excluirLocalTuristico: " . $conexao->error);
        return false;
    }

    $stmt_verificar->bind_param("i", $id);
    $stmt_verificar->execute();
    $resultado_verificar = $stmt_verificar->get_result();
    
    if ($resultado_verificar->num_rows === 0) {
        error_log("Local turístico não encontrado para exclusão: ID " . $id);
        $stmt_verificar->close();
        return false;
    }

    $local = $resultado_verificar->fetch_assoc();
    $stmt_verificar->close();

    // Verifica se o usuário tem permissão para excluir (é o proprietário)
    if ($local['usuario_id'] != $usuario_id) {
        error_log("Usuário " . $usuario_id . " tentou excluir local turístico " . $id . " que não lhe pertence");
        return false;
    }

    // Exclui o local turístico
    $sql_delete = "DELETE FROM localturismo WHERE idLocal = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    if (!$stmt_delete) {
        error_log("Erro ao preparar delete em excluirLocalTuristico: " . $conexao->error);
        return false;
    }

    $stmt_delete->bind_param("i", $id);
    $resultado_delete = $stmt_delete->execute();
    if ($resultado_delete === false) {
        error_log("Erro ao executar delete em excluirLocalTuristico: " . $stmt_delete->error);
    }
    $stmt_delete->close();

    // Se a exclusão foi bem-sucedida, também exclui o contato associado
    if ($resultado_delete && $local['contato_id']) {
        $sql_delete_contato = "DELETE FROM contato WHERE idContato = ?";
        $stmt_delete_contato = $conexao->prepare($sql_delete_contato);
        if ($stmt_delete_contato) {
            $stmt_delete_contato->bind_param("i", $local['contato_id']);
            $stmt_delete_contato->execute();
            $stmt_delete_contato->close();
        }
    }

    return $resultado_delete;
}

// Função para verificar se um evento pertence ao usuário
function verificarPropriedadeEvento($conexao, $evento_id, $usuario_id) {
    if (!$conexao || $conexao->connect_error) {
        return false;
    }

    $sql = "SELECT usuario_id FROM eventos WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        return false;
    }
    $evento = $resultado->fetch_assoc();
    $stmt->close();
    return $evento['usuario_id'] == $usuario_id;
}
// Função para verificar se um local turístico pertence ao usuário
function verificarPropriedadeLocalTuristico($conexao, $local_id, $usuario_id) {
    if (!$conexao || $conexao->connect_error) {
        return false;
    }
    $sql = "SELECT usuario_id FROM localturismo WHERE idLocal = ?";
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("i", $local_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 0) {
        $stmt->close();
        return false;
    }
    $local = $resultado->fetch_assoc();
    $stmt->close();
    return $local['usuario_id'] == $usuario_id;
}
?>