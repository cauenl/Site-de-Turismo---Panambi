<?php
// Funções administrativas para o sistema

// Verificar se o usuário é administrador
function isAdmin($conexao, $usuario_id) {
    if (!$conexao || $conexao->connect_error || !$usuario_id) {
        return false;
    }
    
    $sql = "SELECT p.nome FROM usuario u 
            INNER JOIN perfil p ON u.perfil_id = p.id 
            WHERE u.id = ? AND p.nome = 'admin'";
    
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        error_log("Erro ao preparar statement em isAdmin: " . $conexao->error);
        return false;
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
    
    return $resultado->num_rows > 0;
}

// Listar eventos pendentes de aprovação
function listarEventosPendentes($conexao) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarEventosPendentes: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }
    
    // Exemplo de JOIN na função de listagem de eventos
    $sql = "SELECT e.*, l.nome AS nome_local
            FROM eventos e
            LEFT JOIN localevento l ON e.localEvento_idlocalEvento = l.idlocalEvento
            WHERE e.aprovado = 0
            ORDER BY e.data_inicial ASC";
    
    $resultado = $conexao->query($sql);
    $eventos = [];
    
    if ($resultado === false) {
        error_log("Erro ao executar query em listarEventosPendentes: " . $conexao->error);
        return [];
    } elseif ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $eventos[] = $row;
        }
    }
    
    return $eventos;
}

// Listar eventos aprovados
function listarEventosAprovados($conexao) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarEventosAprovados: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }
    
    $sql = "SELECT e.*, u.nome as nome_usuario, a.nome as aprovado_por_nome
            FROM eventos e 
            LEFT JOIN usuario u ON e.usuario_id = u.id 
            LEFT JOIN usuario a ON e.aprovado_por = a.id
            WHERE e.aprovado = 1 
            ORDER BY e.data_aprovacao DESC";
    
    $resultado = $conexao->query($sql);
    $eventos = [];
    
    if ($resultado === false) {
        error_log("Erro ao executar query em listarEventosAprovados: " . $conexao->error);
        return [];
    } elseif ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $eventos[] = $row;
        }
    }
    
    return $eventos;
}

// Aprovar evento
function aprovarEvento($conexao, $evento_id, $admin_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em aprovarEvento: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    
    $sql = "UPDATE eventos SET aprovado = 1, data_aprovacao = NOW(), aprovado_por = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar statement em aprovarEvento: " . $conexao->error);
        return false;
    }
    
    $stmt->bind_param("ii", $admin_id, $evento_id);
    $resultado = $stmt->execute();
    
    if ($resultado === false) {
        error_log("Erro ao executar statement em aprovarEvento: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

// Rejeitar evento
function rejeitarEvento($conexao, $evento_id, $admin_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em rejeitarEvento: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    
    $sql = "UPDATE eventos SET aprovado = 2, data_aprovacao = NOW(), aprovado_por = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar statement em rejeitarEvento: " . $conexao->error);
        return false;
    }
    
    $stmt->bind_param("ii", $admin_id, $evento_id);
    $resultado = $stmt->execute();
    
    if ($resultado === false) {
        error_log("Erro ao executar statement em rejeitarEvento: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

// Excluir evento (admin)
function excluirEventoAdmin($conexao, $evento_id, $base_path) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em excluirEventoAdmin: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    
    // Buscar caminho da imagem antes de excluir
    $caminho_imagem_relativo = null;
    $sql_select = "SELECT imagem FROM eventos WHERE id = ?";
    $stmt_select = $conexao->prepare($sql_select);
    
    if ($stmt_select) {
        $stmt_select->bind_param("i", $evento_id);
        if ($stmt_select->execute()) {
            $stmt_select->bind_result($caminho_imagem_relativo);
            $stmt_select->fetch();
        }
        $stmt_select->close();
    }
    
    // Excluir evento
    $sql_delete = "DELETE FROM eventos WHERE id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    
    if (!$stmt_delete) {
        error_log("Erro ao preparar delete em excluirEventoAdmin: " . $conexao->error);
        return false;
    }
    
    $stmt_delete->bind_param("i", $evento_id);
    $resultado_delete = $stmt_delete->execute();
    
    if ($resultado_delete === false) {
        error_log("Erro ao executar delete em excluirEventoAdmin: " . $stmt_delete->error);
    }
    
    $stmt_delete->close();
    
    // Excluir arquivo de imagem se existir
    if ($resultado_delete && $caminho_imagem_relativo) {
        $caminho_imagem_abs = rtrim($base_path, '/') . '/' . $caminho_imagem_relativo;
        if (file_exists($caminho_imagem_abs)) {
            if (!unlink($caminho_imagem_abs)) {
                error_log("Erro ao excluir arquivo de imagem: " . $caminho_imagem_abs);
            }
        }
    }
    
    return $resultado_delete;
}

// Buscar evento por ID
function buscarEventoPorId($conexao, $evento_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em buscarEventoPorId: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return null;
    }
    
    $sql = "SELECT * FROM eventos WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar statement em buscarEventoPorId: " . $conexao->error);
        return null;
    }
    
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $evento = null;
    if ($resultado->num_rows > 0) {
        $evento = $resultado->fetch_assoc();
    }
    
    $stmt->close();
    return $evento;
}

// Atualizar evento (admin)
function atualizarEventoAdmin($conexao, $evento_id, $dados, $caminho_imagem_relativo = null) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em atualizarEventoAdmin: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    
    // Se uma nova imagem foi enviada, incluir no UPDATE
    if ($caminho_imagem_relativo !== null) {
        $sql = "UPDATE eventos SET 
                nome_responsavel = ?, 
                email = ?, 
                nome_evento = ?, 
                data_inicial = ?, 
                data_final = ?, 
                horario = ?, 
                localEvento_idlocalEvento = ?, 
                pago = ?, 
                valor = ?, 
                categoria = ?, 
                info_descricao = ?, 
                imagem = ?
                WHERE id = ?";
        
        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            error_log("Erro ao preparar statement em atualizarEventoAdmin: " . $conexao->error);
            return false;
        }
        
        $stmt->bind_param(
            "sssssssidssi",
            $dados['nome_responsavel'],
            $dados['email'],
            $dados['nome_evento'],
            $dados['data_inicial'],
            $dados['data_final'],
            $dados['horario'],
            $dados['local'],
            $dados['pago'],
            $dados['valor'],
            $dados['categoria'],
            $dados['info_descricao'],
            $caminho_imagem_relativo,
            $evento_id
        );
    } else {
        // Atualizar sem modificar a imagem
        $sql = "UPDATE eventos SET 
                nome_responsavel = ?, 
                email = ?, 
                nome_evento = ?, 
                data_inicial = ?, 
                data_final = ?, 
                horario = ?, 
                localEvento_idlocalEvento = ?, 
                pago = ?, 
                valor = ?, 
                categoria = ?, 
                info_descricao = ?
                WHERE id = ?";
        
        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            error_log("Erro ao preparar statement em atualizarEventoAdmin: " . $conexao->error);
            return false;
        }
        
        $stmt->bind_param(
            "ssssssssdssi",
            $dados['nome_responsavel'],
            $dados['email'],
            $dados['nome_evento'],
            $dados['data_inicial'],
            $dados['data_final'],
            $dados['horario'],
            $dados['local'],
            $dados['pago'],
            $dados['valor'],
            $dados['categoria'],
            $dados['info_descricao'],
            $evento_id
        );
    }
    
    $resultado = $stmt->execute();
    if ($resultado === false) {
        error_log("Erro ao executar statement em atualizarEventoAdmin: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

// Listar locais turísticos pendentes
function listarLocaisTuristicosPendentes($conexao) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarLocaisTuristicosPendentes: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }
    
    $sql = "SELECT l.*, u.nome as nome_usuario 
            FROM localturismo l 
            LEFT JOIN usuario u ON l.usuario_id = u.id 
            WHERE l.aprovado = 0 
            ORDER BY l.data_cadastro DESC";
    
    $resultado = $conexao->query($sql);
    $locais = [];
    
    if ($resultado === false) {
        error_log("Erro ao executar query em listarLocaisTuristicosPendentes: " . $conexao->error);
        return [];
    } elseif ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $locais[] = $row;
        }
    }
    
    return $locais;
}

// Aprovar local turístico
function aprovarLocalTuristico($conexao, $local_id, $admin_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em aprovarLocalTuristico: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    
    $sql = "UPDATE localturismo SET aprovado = 1, data_aprovacao = NOW(), aprovado_por = ? WHERE idLocal = ?";
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar statement em aprovarLocalTuristico: " . $conexao->error);
        return false;
    }
    
    $stmt->bind_param("ii", $admin_id, $local_id);
    $resultado = $stmt->execute();
    
    if ($resultado === false) {
        error_log("Erro ao executar statement em aprovarLocalTuristico: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

// Reprovar local turístico
function reprovarLocalTuristico($conexao, $local_id, $admin_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em reprovarLocalTuristico: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    $sql = "UPDATE localturismo SET aprovado = 2, data_aprovacao = NOW(), aprovado_por = ? WHERE idLocal = ?";
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        error_log("Erro ao preparar statement em reprovarLocalTuristico: " . $conexao->error);
        return false;
    }
    $stmt->bind_param("ii", $admin_id, $local_id);
    $resultado = $stmt->execute();
    if ($resultado === false) {
        error_log("Erro ao executar statement em reprovarLocalTuristico: " . $stmt->error);
    }
    $stmt->close();
    return $resultado;
}

// Excluir local turístico (admin)
function excluirLocalTuristicoAdmin($conexao, $local_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em excluirLocalTuristicoAdmin: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }
    
    $sql = "DELETE FROM localturismo WHERE idLocal = ?";
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar delete em excluirLocalTuristicoAdmin: " . $conexao->error);
        return false;
    }
    
    $stmt->bind_param("i", $local_id);
    $resultado = $stmt->execute();
    
    if ($resultado === false) {
        error_log("Erro ao executar delete em excluirLocalTuristicoAdmin: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

// Modificar função listarEventos para mostrar apenas eventos aprovados
function listarEventosAprovadosPublico($conexao) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarEventosAprovadosPublico: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }

    $sql = "SELECT * FROM eventos WHERE aprovado = 1 ORDER BY data_inicial ASC";
    $resultado = $conexao->query($sql);

    $eventos = [];

    if ($resultado === false) {
        error_log("Erro ao executar query em listarEventosAprovadosPublico: " . $conexao->error);
        return [];
    } elseif ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $eventos[] = $row;
        }
    }

    return $eventos;
}

// Listar locais turísticos aprovados
function listarLocaisTuristicosAprovados($conexao) {
    $locais = [];
    $sql = "SELECT l.*, u.nome AS nome_usuario, a.nome AS aprovado_por_nome
            FROM localturismo l
            LEFT JOIN usuario u ON l.usuario_id = u.id
            LEFT JOIN usuario a ON l.aprovado_por = a.id
            WHERE l.aprovado = 1
            ORDER BY l.data_aprovacao DESC";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $locais[] = $row;
        }
    }
    return $locais;
}
?>