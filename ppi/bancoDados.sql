-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema ppi
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema ppi
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `ppi` DEFAULT CHARACTER SET utf8mb4 ;
USE `ppi` ;

-- -----------------------------------------------------
-- Table `ppi`.`contato`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ppi`.`contato` (
  `idContato` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`idContato`))
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `ppi`.`perfil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ppi`.`perfil` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `nome` (`nome` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `ppi`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ppi`.`usuario` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) NULL DEFAULT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `tipo_usuario` ENUM('comum', 'empresa', 'admin') NULL DEFAULT NULL,
  `perfil_id` INT(11) NULL DEFAULT NULL,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email` ASC),
  INDEX `perfil_id` (`perfil_id` ASC),
  CONSTRAINT `usuario_ibfk_1`
    FOREIGN KEY (`perfil_id`)
    REFERENCES `ppi`.`perfil` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `ppi`.`localevento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ppi`.`localevento` (
  `idlocalEvento` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NULL DEFAULT NULL,
  `logradouro` VARCHAR(45) NULL DEFAULT NULL,
  `numero` VARCHAR(45) NULL DEFAULT NULL,
  `telefone` INT(11) NULL DEFAULT NULL,
  `contato_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`idlocalEvento`),
  INDEX `contato_id` (`contato_id` ASC),
  CONSTRAINT `localevento_ibfk_1`
    FOREIGN KEY (`contato_id`)
    REFERENCES `ppi`.`contato` (`idContato`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `ppi`.`eventos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ppi`.`eventos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_responsavel` VARCHAR(100) NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `nome_evento` VARCHAR(100) NULL DEFAULT NULL,
  `data_inicial` DATE NULL DEFAULT NULL,
  `data_final` DATE NULL DEFAULT NULL,
  `horario` TIME NULL DEFAULT NULL,
  `pago` TINYINT(1) NULL DEFAULT NULL,
  `valor` DECIMAL(10,2) NULL DEFAULT NULL,
  `categoria` VARCHAR(50) NULL DEFAULT NULL,
  `imagem` VARCHAR(255) NULL DEFAULT NULL,
  `usuario_id` INT(11) NULL DEFAULT NULL,
  `info_descricao` TEXT NULL DEFAULT NULL,
  `localEvento_idlocalEvento` INT(11) NULL DEFAULT NULL,
  `aprovado` TINYINT(1) NULL DEFAULT NULL,
  `data_aprovacao` TIMESTAMP NULL DEFAULT NULL,
  `aprovado_por` INT(11) NULL DEFAULT NULL,
  `data_cadastro` DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `usuario_id` (`usuario_id` ASC),
  INDEX `localEvento_idlocalEvento` (`localEvento_idlocalEvento` ASC),
  INDEX `aprovado_por` (`aprovado_por` ASC),
  CONSTRAINT `eventos_ibfk_1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `ppi`.`usuario` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `eventos_ibfk_2`
    FOREIGN KEY (`localEvento_idlocalEvento`)
    REFERENCES `ppi`.`localevento` (`idlocalEvento`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `eventos_ibfk_3`
    FOREIGN KEY (`aprovado_por`)
    REFERENCES `ppi`.`usuario` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `ppi`.`localturismo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ppi`.`localturismo` (
  `idLocal` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NULL DEFAULT NULL,
  `descricao` VARCHAR(45) NULL DEFAULT NULL,
  `endereco` VARCHAR(100) NULL DEFAULT NULL,
  `categoria` VARCHAR(45) NULL DEFAULT NULL,
  `tipo` ENUM('ponto_turistico', 'restaurante', 'hotel') NULL DEFAULT NULL,
  `dias_fechado` VARCHAR(45) NULL DEFAULT NULL,
  `imagem` VARCHAR(255) NULL DEFAULT NULL,
  `usuario_id` INT(11) NULL DEFAULT NULL,
  `contato_id` INT(11) NULL DEFAULT NULL,
  `aprovado` TINYINT(1) NULL DEFAULT 0,
  `data_aprovacao` DATETIME NULL DEFAULT NULL,
  `aprovado_por` INT(11) NULL DEFAULT NULL,
  `data_cadastro` DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`idLocal`),
  INDEX `usuario_id` (`usuario_id` ASC),
  INDEX `contato_id` (`contato_id` ASC),
  CONSTRAINT `localturismo_ibfk_1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `ppi`.`usuario` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `localturismo_ibfk_2`
    FOREIGN KEY (`contato_id`)
    REFERENCES `ppi`.`contato` (`idContato`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 11
DEFAULT CHARACTER SET = utf8mb4;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- Garantir que o perfil admin existe
INSERT IGNORE INTO perfil (id, nome) VALUES (1, 'admin');

-- Inserir usuário administrador (senha: admin123)
INSERT INTO usuario (nome, email, telefone, senha, perfil_id) 
VALUES ('Administrador', 'admin@panambi.rs.gov.br', '55999999999', 'admin123', 1)
ON DUPLICATE KEY UPDATE perfil_id = 1;