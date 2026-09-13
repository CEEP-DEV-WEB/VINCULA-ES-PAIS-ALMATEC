CREATE DATABASE vinculacaoPais;
USE vinculacaoPais;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) NOT NULL
);

CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    telefone VARCHAR(20),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE responsavel_aluno (
    id INT AUTO_INCREMENT PRIMARY KEY,
    responsavel_id INT NOT NULL,
    aluno_id INT NOT NULL,
    FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    UNIQUE (responsavel_id, aluno_id)
);

CREATE TABLE presencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    data_aula DATE NOT NULL,
    presente BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
);

CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    nota1 DECIMAL(4,2) NOT NULL,
    nota2 DECIMAL(4,2) NOT NULL,
    nota3 DECIMAL(4,2) NOT NULL,
    nota4 DECIMAL(4,2) NOT NULL,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
);

CREATE TABLE rotina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    dia_semana VARCHAR(20) NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    sala VARCHAR(30),
    professor VARCHAR(100),
    atividade VARCHAR(255),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
);

-- PROFESSORES
CREATE TABLE professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- TURMAS
CREATE TABLE turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- PROFESSOR x DISCIPLINA x TURMA
CREATE TABLE professor_disciplina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    turma_id INT NOT NULL,

    FOREIGN KEY (professor_id) REFERENCES professores(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id),
    FOREIGN KEY (turma_id) REFERENCES turmas(id)
);

CREATE TABLE justificativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    presenca_id INT NOT NULL,
    motivo TEXT NOT NULL,
    status ENUM('Pendente','Aceita','Recusada') DEFAULT 'Pendente',
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    FOREIGN KEY (presenca_id) REFERENCES presencas(id)
);


CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);


CREATE TABLE professor_turma_disciplina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    turma_id INT NOT NULL,
    disciplina_id INT NOT NULL,

    FOREIGN KEY (professor_id) REFERENCES professores(id),
    FOREIGN KEY (turma_id) REFERENCES turmas(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id),

    UNIQUE (professor_id, turma_id, disciplina_id)
);

INSERT INTO usuarios (nome, email, senha, tipo)
VALUES
('Maria Silva', 'maria@email.com', '123456', 'responsavel'),
('João Silva', 'joao@email.com', '123456', 'aluno');



INSERT INTO alunos (usuario_id, matricula)
VALUES (2, '2026001');


INSERT INTO responsaveis (usuario_id, telefone)
VALUES (1, '75999999999');

INSERT INTO responsavel_aluno (responsavel_id, aluno_id)
VALUES (1, 1);

INSERT INTO disciplinas (nome)
VALUES
('Português'),
('Matemática'),
('Informática'),
('Banco de Dados'),
('Programação');

INSERT INTO resultados 
(aluno_id, disciplina_id, nota1, nota2, nota3, nota4)
VALUES
(1, 1, 8.50, 9.00, 7.50, 9.00),
(1, 2, 7.00, 8.50, 8.00, 9.00),
(1, 3, 9.00, 9.50, 8.50, 10.00),
(1, 4, 8.00, 7.50, 9.00, 8.50),
(1, 5, 9.50, 9.00, 9.50, 10.00);

INSERT INTO presencas
(aluno_id, disciplina_id, data_aula, presente)
VALUES
(1, 1, '2026-08-03', TRUE),
(1, 1, '2026-08-10', TRUE),
(1, 1, '2026-08-17', FALSE),
(1, 1, '2026-08-24', TRUE),

(1, 2, '2026-08-04', TRUE),
(1, 2, '2026-08-11', TRUE),
(1, 2, '2026-08-18', TRUE),
(1, 2, '2026-08-25', FALSE),

(1, 3, '2026-08-05', TRUE),
(1, 3, '2026-08-12', TRUE),
(1, 3, '2026-08-19', TRUE),
(1, 3, '2026-08-26', TRUE),

(1, 4, '2026-08-06', TRUE),
(1, 4, '2026-08-13', FALSE),
(1, 4, '2026-08-20', TRUE),
(1, 4, '2026-08-27', TRUE),

(1, 5, '2026-08-07', TRUE),
(1, 5, '2026-08-14', TRUE),
(1, 5, '2026-08-21', TRUE),
(1, 5, '2026-08-28', TRUE);

INSERT INTO rotina
(aluno_id, disciplina_id, dia_semana, horario_inicio, horario_fim, sala, professor, atividade)
VALUES

(1, 1, 'Segunda-feira', '07:00', '07:50', 'Sala 01', 'Ana Oliveira', 'Leitura e interpretação de texto'),

(1, 2, 'Segunda-feira', '07:50', '08:40', 'Sala 02', 'Carlos Santos', 'Exercícios de matemática'),

(1, 3, 'Terça-feira', '07:00', '07:50', 'Lab. 01', 'Marcos Silva', 'Introdução à informática'),

(1, 4, 'Terça-feira', '07:50', '08:40', 'Lab. 02', 'Juliana Souza', 'Modelagem de banco de dados'),

(1, 5, 'Quarta-feira', '07:00', '07:50', 'Lab. 01', 'Rafael Lima', 'Lógica de programação'),

(1, 1, 'Quarta-feira', '07:50', '08:40', 'Sala 01', 'Ana Oliveira', 'Produção textual'),

(1, 2, 'Quinta-feira', '07:00', '07:50', 'Sala 02', 'Carlos Santos', 'Equações e problemas'),

(1, 3, 'Quinta-feira', '07:50', '08:40', 'Lab. 01', 'Marcos Silva', 'Sistemas operacionais'),

(1, 4, 'Sexta-feira', '07:00', '07:50', 'Lab. 02', 'Juliana Souza', 'Consultas SQL'),

(1, 5, 'Sexta-feira', '07:50', '08:40', 'Lab. 01', 'Rafael Lima', 'Desenvolvimento web');



INSERT INTO usuarios (nome, email, senha, tipo)
VALUES
('Carlos Santos', 'carlos@email.com', '123456', 'professor');


INSERT INTO usuarios (nome, email, senha, tipo)
VALUES
('Ana Oliveira', 'ana@escola.com', '123456', 'professor'),
('Carlos Santos', 'carlos@escola.com', '123456', 'professor'),
('Marcos Silva', 'marcos@escola.com', '123456', 'professor'),
('Juliana Souza', 'juliana@escola.com', '123456', 'professor'),
('Rafael Lima', 'rafael@escola.com', '123456', 'professor');

INSERT INTO cursos (nome)
VALUES
('Informática'),
('Mecatrônica'),
('Eletromecânica'),
('Logística'),
('Edificações'),
('Segurança do Trabalho');

INSERT INTO professores (usuario_id)
VALUES (3);


INSERT INTO professores (usuario_id)
SELECT id
FROM usuarios
WHERE tipo = 'professor';


INSERT INTO professor_turma_disciplina
(professor_id, turma_id, disciplina_id)


INSERT INTO justificativas
(aluno_id, presenca_id, motivo)
VALUES
(1, 3, 'Problema de saúde e não pôde comparecer à aula.');


SELECT
    p.id,
    t.id,
    d.id

FROM professores p
JOIN usuarios u ON u.id = p.usuario_id
JOIN turmas t ON t.identificacao = '1º Informática'
JOIN disciplinas d

WHERE
    (u.nome = 'Ana Oliveira' AND d.nome = 'Português')
    OR
    (u.nome = 'Carlos Santos' AND d.nome = 'Matemática')
    OR
    (u.nome = 'Marcos Silva' AND d.nome = 'Informática')
    OR
    (u.nome = 'Juliana Souza' AND d.nome = 'Banco de Dados')
    OR
    (u.nome = 'Rafael Lima' AND d.nome = 'Programação');



ALTER TABLE turmas
MODIFY COLUMN nome VARCHAR(100) NULL;

select * from turmas;

ALTER TABLE turmas
ADD COLUMN curso_id INT,
ADD COLUMN serie INT,
ADD COLUMN identificacao VARCHAR(20);

ALTER TABLE turmas
ADD CONSTRAINT fk_turmas_curso
FOREIGN KEY (curso_id) REFERENCES cursos(id);

ALTER TABLE alunos
ADD COLUMN turma_id INT;

ALTER TABLE alunos
ADD CONSTRAINT fk_alunos_turma
FOREIGN KEY (turma_id) REFERENCES turmas(id);

UPDATE alunos SET turma_id = 1
WHERE id = 1;

SELECT * FROM alunos;

SELECT id, nome, email, tipo
FROM usuarios
WHERE tipo = 'professor';


SELECT
    p.id AS professor_id,
    u.nome AS professor,
    t.identificacao AS turma,
    d.nome AS disciplina
FROM professor_turma_disciplina ptd
JOIN professores p ON p.id = ptd.professor_id
JOIN usuarios u ON u.id = p.usuario_id
JOIN turmas t ON t.id = ptd.turma_id
JOIN disciplinas d ON d.id = ptd.disciplina_id;


ALTER TABLE justificativas
ADD COLUMN observacao_professor TEXT;


ALTER TABLE presencas
ADD UNIQUE (aluno_id, disciplina_id, data_aula);