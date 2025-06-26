<?php
// Funções relacionadas ao gerenciamento de locais

// Função para verificar se um local existe na tabela localevento
function verificarLocalExiste($conexao, $nome_local) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em verificarLocalExiste: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    $sql = "SELECT idlocalEvento FROM localevento WHERE nome = ?";
    $stmt = $conexao->prepare($sql);
    
    if ($stmt === false) {
        error_log("Erro ao preparar statement em verificarLocalExiste: " . $conexao->error);
        return false;
    }

    $stmt->bind_param("s", $nome_local);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['idlocalEvento'];
    } else {
        $stmt->close();
        return false;
    }
}

// Função para adicionar um novo contato
function adicionarContato($conexao, $nome, $email, $telefone) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em adicionarContato: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    $sql = "INSERT INTO contato (nome, email, telefone) VALUES (?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    
    if ($stmt === false) {
        error_log("Erro ao preparar statement em adicionarContato: " . $conexao->error);
        return false;
    }

    $stmt->bind_param("sss", $nome, $email, $telefone);
    
    if ($stmt->execute()) {
        $id_contato = $stmt->insert_id;
        $stmt->close();
        return $id_contato;
    } else {
        error_log("Erro ao executar statement em adicionarContato: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

// Função para adicionar um novo local
function adicionarLocal($conexao, $nome, $logradouro, $numero, $contato_id) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em adicionarLocal: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return false;
    }

    $sql = "INSERT INTO localevento (nome, logradouro, numero, contato_id) VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    
    if ($stmt === false) {
        error_log("Erro ao preparar statement em adicionarLocal: " . $conexao->error);
        return false;
    }

    $stmt->bind_param("sssi", $nome, $logradouro, $numero, $contato_id);
    
    if ($stmt->execute()) {
        $id_local = $stmt->insert_id;
        $stmt->close();
        return $id_local;
    } else {
        error_log("Erro ao executar statement em adicionarLocal: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

// Função para listar todos os locais
function listarLocais($conexao) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Erro de conexão em listarLocais: " . ($conexao ? $conexao->connect_error : 'Conexão inválida'));
        return [];
    }

    $sql = "SELECT l.*, c.nome as contato_nome, c.email, c.telefone 
            FROM localevento l 
            JOIN contato c ON l.contato_id = c.id 
            ORDER BY l.nome ASC";
    
    $resultado = $conexao->query($sql);
    $locais = [];

    if ($resultado === false) {
        error_log("Erro ao executar query em listarLocais: " . $conexao->error);
        return [];
    } elseif ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $locais[] = $row;
        }
    }

    return $locais;
}
?>
