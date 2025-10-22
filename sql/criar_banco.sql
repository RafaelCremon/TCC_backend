-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS quantumdb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Selecionar o banco de dados
USE quantumdb;

-- Criação da tabela de instituições
CREATE TABLE IF NOT EXISTS instituicoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    estado VARCHAR(100),
    cep VARCHAR(10),
    telefone VARCHAR(20),
    email VARCHAR(100),
    subdominio VARCHAR(255) NOT NULL UNIQUE, -- Ex: colegio-cruzeiro
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criação da tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instituicao_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL, -- Deve ser criptografada no backend
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- Criar a tabela de professores
CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instituicao_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL, -- senha criptografada
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Criar a tabela de alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instituicao_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    telefone VARCHAR(20),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Inserir instituição Cruzeiro do Sul
INSERT INTO instituicoes (
    nome, 
    cnpj, 
    endereco, 
    cidade, 
    estado, 
    cep, 
    telefone, 
    email, 
    subdominio
) VALUES (
    'Colégio Cruzeiro do Sul',
    '11.111.111/1111-11',
    'Vila Jacuí, Av. Dr. Ussiel Ciril',
    'São Paulo',
    'SP',
    '08060-070',
    '(11) 20375-777',
    'cruzeiroeducacional@cruzei',
    'colegio-cruzeiro'
);

-- Inserir usuário
INSERT INTO usuarios (
    instituicao_id,
    nome,
    usuario,
    email,
    telefone,
    senha
) VALUES (
    1, -- id da instituição Colégio Cruzeiro do Sul
    'Rafael Cremon',
    'RafaelCremon',
    'rafaelcremon10@gmail.com',
    '(11) 94136-0669',
    MD5('12345678') -- senha criptografada
);
