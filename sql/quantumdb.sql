
-- COPIAR ESSE CODIGO NO PHPMYSQL BANCO DO XAMPP PARA CRIAR 
-- O BANCO DE DADOS COMPLETO 
-- NAO TEM NENHUM USUARIO CADASTRADO FAÇA NO PAINEL ADMIN
-- Banco de dados: quantumdb
--

CREATE DATABASE IF NOT EXISTS `quantumdb` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_general_ci;

USE `quantumdb`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `avisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) NOT NULL,
  `conteudo` text NOT NULL,
  `data` datetime NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `data_inicial` date DEFAULT NULL,
  `data_limite` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `instituicoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subdominio` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`),
  UNIQUE KEY `subdominio` (`subdominio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instituicao_id` int(11) NOT NULL,
  `classe` varchar(100) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atalhos` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `instituicao_id` (`instituicao_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instituicao_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_evento` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fim` time DEFAULT NULL,
  `tipo` varchar(50) DEFAULT 'evento',
  `cor` varchar(7) DEFAULT '#0057ff',
  `criado_por` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `instituicao_id` (`instituicao_id`),
  KEY `criado_por` (`criado_por`),
  CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `eventos_ibfk_2` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;