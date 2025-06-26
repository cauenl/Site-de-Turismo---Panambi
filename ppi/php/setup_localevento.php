<?php
// Arquivo para criar a tabela localevento e adicionar a coluna na tabela eventos

require_once(__DIR__ . '/config.php');

// Verifica se a conexão com o banco foi estabelecida
if (!$conexao || $conexao->connect_error) {
    die("Erro crítico: Não foi possível conectar ao banco de dados. Detalhes: " . ($conexao ? $conexao->connect_error : 'Variável de conexão não encontrada'));
}

// Criar tabela localevento se não existir
$sql_create_localevento = "
CREATE TABLE IF NOT EXISTS `localevento` (
  `idlocalEvento` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `logradouro` varchar(100) NOT NULL,
  `numero` varchar(45) NOT NULL,
  `contato_id` int(11) NOT NULL,
  PRIMARY KEY (`idlocalEvento`),
  KEY `fk_localevento_contato` (`contato_id`),
  CONSTRAINT `fk_localevento_contato` FOREIGN KEY (`contato_id`) REFERENCES `contato` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conexao->query($sql_create_localevento) === TRUE) {
    echo "Tabela localevento criada com sucesso ou já existe.<br>";
} else {
    echo "Erro ao criar tabela localevento: " . $conexao->error . "<br>";
}

// Verificar se a coluna localEvento_idlocalEvento já existe na tabela eventos
$sql_check_column = "SHOW COLUMNS FROM `eventos` LIKE 'localEvento_idlocalEvento'";
$result = $conexao->query($sql_check_column);

if ($result->num_rows == 0) {
    // Adicionar coluna localEvento_idlocalEvento à tabela eventos
    $sql_alter_eventos = "
    ALTER TABLE `eventos` 
    ADD COLUMN `localEvento_idlocalEvento` int(11) NULL AFTER `local`,
    ADD CONSTRAINT `fk_eventos_localevento` FOREIGN KEY (`localEvento_idlocalEvento`) REFERENCES `localevento` (`idlocalEvento`) ON DELETE SET NULL ON UPDATE CASCADE;
    ";

    if ($conexao->query($sql_alter_eventos) === TRUE) {
        echo "Coluna localEvento_idlocalEvento adicionada à tabela eventos com sucesso.<br>";
    } else {
        echo "Erro ao adicionar coluna à tabela eventos: " . $conexao->error . "<br>";
    }
} else {
    echo "Coluna localEvento_idlocalEvento já existe na tabela eventos.<br>";
}

$conexao->close();
echo "Configuração concluída.";
?>
