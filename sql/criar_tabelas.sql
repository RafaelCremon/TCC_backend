-- Criação da tabela de instituições
CREATE TABLE instituicoes (
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

-- Criação da tabela de administradores
CREATE TABLE administradores (
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

-- Dados usuário administrador
INSERT INTO administradores (
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
