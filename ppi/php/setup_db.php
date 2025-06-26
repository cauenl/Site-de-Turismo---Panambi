<?php
// Script para criar o banco de dados e a tabela de eventos

// Configurações de conexão
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Conecta ao servidor MySQL
    $conn = new mysqli($host, $user, $pass);
    
    // Verifica se houve erro na conexão
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
    
    // Cria o banco de dados se não existir
    $sql = "CREATE DATABASE IF NOT EXISTS turismo_db";
    if ($conn->query($sql) === TRUE) {
        echo "Banco de dados criado ou já existente com sucesso!\n";
    } else {
        echo "Erro ao criar banco de dados: " . $conn->error . "\n";
    }
    
    // Seleciona o banco de dados
    $conn->select_db("turismo_db");
    
    // Cria a tabela de eventos
    $sql = "CREATE TABLE IF NOT EXISTS eventos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_responsavel VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        nome_evento VARCHAR(255) NOT NULL,
        data_inicial DATE NOT NULL,
        data_final DATE NOT NULL,
        horario TIME NOT NULL,
        local VARCHAR(255) NOT NULL,
        pagamento TEXT NOT NULL,
        descricao TEXT NOT NULL,
        info_adicional TEXT,
        imagem VARCHAR(255) NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Tabela 'eventos' criada ou já existente com sucesso!\n";
    } else {
        echo "Erro ao criar tabela: " . $conn->error . "\n";
    }
    
    // Fecha a conexão
    $conn->close();
    
    echo "Configuração do banco de dados concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
