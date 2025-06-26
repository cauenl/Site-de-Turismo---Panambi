<?php
// Arquivo para criar a tabela localturismo e suas relações

require_once(__DIR__ . '/config.php');

// Verifica se a conexão com o banco foi estabelecida
if (!$conexao || $conexao->connect_error) {
    die("Erro crítico: Não foi possível conectar ao banco de dados. Detalhes: " . ($conexao ? $conexao->connect_error : 'Variável de conexão não encontrada'));
}

// Criar tabela localturismo se não existir
$sql_create_localturismo = "
CREATE TABLE IF NOT EXISTS `localturismo` (
  `idLocal` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `descricao` varchar(45) DEFAULT NULL,
  `endereco` varchar(100) NOT NULL,
  `categoria` varchar(45) NOT NULL,
  `tipo` enum('privado','publico') NOT NULL,
  `dias_fechado` varchar(45) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `contato_id` int(11) NOT NULL,
  PRIMARY KEY (`idLocal`),
  KEY `fk_localturismo_usuario` (`usuario_id`),
  KEY `fk_localturismo_contato` (`contato_id`),
  CONSTRAINT `fk_localturismo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_localturismo_contato` FOREIGN KEY (`contato_id`) REFERENCES `contato` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conexao->query($sql_create_localturismo) === TRUE) {
    echo "Tabela localturismo criada com sucesso ou já existe.<br>";
} else {
    echo "Erro ao criar tabela localturismo: " . $conexao->error . "<br>";
}

$conexao->close();
echo "Configuração concluída.";
?>
